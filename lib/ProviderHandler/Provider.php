<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2019 Felix Nüsse <felix.nuesse@t-online.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Modified for the Splashd fork: the single cached image was replaced by a
 * pool of pre-scaled images so that the background can change on every
 * request, and the remote image providers were removed.
 */

namespace OCA\Splashd\ProviderHandler;

use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

abstract class Provider
{
    const SIZE_SMALL = 0;
    const SIZE_NORMAL = 1;
    const SIZE_HIGH = 2;
    const SIZE_ULTRA = 3;

    /** Longest edge of a stored image, in pixels. */
    const MAX_WIDTH = 2560;
    const MAX_HEIGHT = 1600;

    /** Images narrower than this are not worth a background. */
    const MIN_WIDTH = 640;

    const JPEG_QUALITY = 82;

    /** Name of the sidecar file that holds per-image metadata. */
    const META_FILE = 'pool.json';

    /**
     * Search terms offered to the user, if the provider supports them.
     */
    public string $DEFAULT_SEARCH = '';
    public bool $ALLOW_CUSTOMIZING = false;

    /**
     * Whether images are prepared on the server and served from the app data.
     * Only "NextcloudImage" is not.
     */
    public bool $IS_CACHED = true;

    public string $CACHED_URL = '/index.php/apps/splashd/api/image';
    public string $DEFAULT_METADATA_URL = '';

    /** Upper bound for the number of images held in the pool. */
    public int $POOL_LIMIT = 60;

    public function __construct(
        protected string $appName,
        protected LoggerInterface $logger,
        protected IConfig $config,
        protected IAppData $appData,
        protected string $providerName,
        protected IClientService $clientService,
        protected IRootFolder $rootFolder,
    )
    {
    }

    /**
     * Collect raw images for the pool.
     *
     * Yields ['data' => <binary>, 'meta' => <array>] so that large pools do
     * not have to be held in memory all at once.
     */
    abstract protected function collect(): iterable;

    /**
     * Replace the pool with a freshly collected set of scaled images.
     *
     * @return int number of images now in the pool
     */
    public function fillPool(): int
    {
        $folder = $this->getPoolFolder();
        $meta = [];
        $index = 0;

        try {
            foreach ($this->collect() as $item) {
                if ($index >= $this->POOL_LIMIT) {
                    break;
                }
                $scaled = $this->scale($item['data'] ?? '');
                if ($scaled === null) {
                    continue;
                }
                $index++;
                $name = sprintf('bg-%03d.jpg', $index);
                $folder->newFile($name, $scaled);
                $meta[$name] = $item['meta'] ?? [];
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Splashd: filling the pool for ' . $this->providerName . ' failed: ' . $e->getMessage());
        }

        if ($index === 0) {
            // Nothing could be collected - a network hiccup, an empty folder,
            // a wrong path. Keep whatever is already there rather than
            // leaving the login page without a background.
            $existing = count($this->listPool());
            $this->logger->warning('Splashd: no image collected for ' . $this->providerName
                . ', keeping the ' . $existing . ' image(s) already in the pool');
            return $existing;
        }

        // Remove leftovers of a previous, larger pool.
        foreach ($folder->getDirectoryListing() as $file) {
            $name = $file->getName();
            if ($name === self::META_FILE) {
                continue;
            }
            if (!isset($meta[$name])) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                    // nothing we can do about it, keep going
                }
            }
        }

        $folder->newFile(self::META_FILE, json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->logger->info('Splashd: pool for ' . $this->providerName . ' now holds ' . $index . ' image(s)');

        return $index;
    }

    /**
     * Drop every stored image.
     */
    public function clearPool(): void
    {
        try {
            foreach ($this->getPoolFolder()->getDirectoryListing() as $file) {
                $file->delete();
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Splashd: clearing the pool for ' . $this->providerName . ' failed: ' . $e->getMessage());
        }
    }

    /**
     * Names of the images in the pool, sorted.
     *
     * @return string[]
     */
    public function listPool(): array
    {
        $names = [];
        try {
            foreach ($this->getPoolFolder()->getDirectoryListing() as $file) {
                $name = $file->getName();
                if ($name !== self::META_FILE) {
                    $names[] = $name;
                }
            }
        } catch (\Throwable $e) {
            return [];
        }
        sort($names);
        return $names;
    }

    /**
     * One named image out of the pool, or null when it does not exist.
     */
    public function getPoolFile(string $name): ?ISimpleFile
    {
        if (!in_array($name, $this->listPool(), true)) {
            return null;
        }
        try {
            return $this->getPoolFolder()->getFile($name);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Per-image metadata written by the last fillPool().
     */
    public function getPoolMetadata(string $name): array
    {
        try {
            $raw = $this->getPoolFolder()->getFile(self::META_FILE)->getContent();
        } catch (\Throwable $e) {
            return [];
        }
        $all = json_decode($raw, true);
        if (!is_array($all)) {
            return [];
        }
        return is_array($all[$name] ?? null) ? $all[$name] : [];
    }

    /**
     * Scale to background size and re-encode as JPEG.
     *
     * Re-encoding also drops EXIF, XMP and IPTC, so no camera, GPS or author
     * data from the original file is handed out.
     *
     * @return string|null JPEG data, or null if the input was unusable
     */
    protected function scale(string $binary): ?string
    {
        if ($binary === '') {
            return null;
        }
        if (!function_exists('imagecreatefromstring')) {
            $this->logger->warning('Splashd: PHP GD is missing, storing the image unscaled');
            return $binary;
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < self::MIN_WIDTH) {
            return null;
        }

        $ratio = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height, 1.0);
        $targetWidth = max(1, (int)round($width * $ratio));
        $targetHeight = max(1, (int)round($height * $ratio));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            return null;
        }
        // Flatten any transparency onto white, JPEG has no alpha channel.
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $ok = imagejpeg($target, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();

        if (!$ok || !is_string($jpeg) || $jpeg === '') {
            return null;
        }
        return $jpeg;
    }

    /**
     * Fetch a remote resource through the Nextcloud HTTP client.
     *
     * @return string|false response body, or false on failure
     */
    protected function getData(string $url): string|false
    {
        try {
            $client = $this->clientService->newClient();
            $response = $client->get($url, ['timeout' => 20]);
            return (string)$response->getBody();
        } catch (\Exception $e) {
            $this->logger->warning('Splashd: failed to fetch ' . $url . ': ' . $e->getMessage());
            return false;
        }
    }

    protected function getPoolFolder(): ISimpleFolder
    {
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', $this->providerName);
        try {
            return $this->appData->getFolder($name);
        } catch (NotFoundException $e) {
            return $this->appData->newFolder($name);
        }
    }

    public function setCustomSearchTerms(string $term): void
    {
        if ($this->ALLOW_CUSTOMIZING) {
            $this->config->setAppValue($this->appName, 'splashd/provider/' . $this->providerName . '/searchterms', $term);
        }
    }

    /**
     * The stored search terms. Not filtered.
     */
    public function getCustomSearchterms(): string
    {
        if (!$this->ALLOW_CUSTOMIZING) {
            return '';
        }
        $term = $this->config->getAppValue($this->appName, 'splashd/provider/' . $this->providerName . '/searchterms', $this->DEFAULT_SEARCH);
        return $term === '' ? $this->DEFAULT_SEARCH : $term;
    }

    /**
     * One of the search terms, reduced to letters so that it cannot break
     * out of the query string.
     */
    public function getRandomSearchTerm(): string
    {
        $terms = explode(',', $this->getCustomSearchterms());
        shuffle($terms);
        return preg_replace('/[^a-z-]/i', '', strtolower(trim($terms[0] ?? '')));
    }

    public function getName(): string
    {
        return $this->providerName;
    }

    public function isCustomizable(): bool
    {
        return $this->ALLOW_CUSTOMIZING;
    }

    public function isCached(): bool
    {
        return $this->IS_CACHED;
    }

    public function getCachedImageURL(): string
    {
        return $this->CACHED_URL;
    }

    public function getMetadata(): ProviderMetadata
    {
        return new ProviderMetadata($this->DEFAULT_METADATA_URL, $this->DEFAULT_METADATA_URL, '', '', $this->providerName);
    }

    /**
     * Domains the content security policy has to allow for this provider.
     * Every provider that serves from the app data returns an empty list.
     *
     * @return string[]
     */
    public function getWhitelistResourceUrls(): array
    {
        return [];
    }

    /**
     * Image URL for providers that do not use the pool. The login background
     * from Administration settings > Theming is the fallback everywhere.
     */
    public function getRandomImageUrl($size): string
    {
        return '/index.php/apps/theming/image/background';
    }
}
