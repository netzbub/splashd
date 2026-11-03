<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Controller;

use OCA\Splashd\Services\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoSameSiteCookieRequired;
use OCP\AppFramework\Http\Attribute\NoTwoFactorRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;

/**
 * Serves the background image.
 *
 * This is the only endpoint the login page reaches, and it hands out nothing
 * but the scaled copies in the app data: no file names, no paths, no listing
 * of the source folder.
 *
 * @package OCA\Splashd\Controller
 */
class ImageController extends Controller
{
    /** Where to send the visitor when there is no image at all. */
    const THEMING_FALLBACK = '/index.php/apps/theming/image/background';

    public function __construct(
        $appName,
        IRequest $request,
        private SettingsService $settings,
    )
    {
        parent::__construct($appName, $request);
    }

    /**
     * One image out of the pool.
     *
     * In mode "request" a different image is picked on every call and the
     * answer must not be cached, so that each login shows a new background.
     * In mode "daily" the same image is served all day and may be cached.
     */
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoSameSiteCookieRequired]
    #[NoTwoFactorRequired]
    public function get(): FileDisplayResponse|RedirectResponse
    {
        $provider = $this->settings->getSelectedImageProvider();
        $names = $provider->isCached() ? $provider->listPool() : [];

        if ($names === [] && $provider->isCached()) {
            // First call after an install or a settings change.
            $this->settings->refillPool();
            $names = $provider->listPool();
        }

        if ($names === []) {
            return new RedirectResponse(self::THEMING_FALLBACK);
        }

        $daily = $this->settings->getChangeMode() === 'daily';
        $name = $daily
            ? $names[crc32(date('Y-m-d')) % count($names)]
            : $names[random_int(0, count($names) - 1)];

        $file = $provider->getPoolFile($name);
        if ($file === null) {
            return new RedirectResponse(self::THEMING_FALLBACK);
        }

        $response = new FileDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/jpeg']);
        if ($daily) {
            $response->cacheFor(3600);
        } else {
            $response->addHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->addHeader('Pragma', 'no-cache');
        }
        return $response;
    }

    /**
     * Source and licence of the current image source.
     *
     * Only CC0 and Public Domain Mark are requested from Openverse, so no
     * attribution is owed; creator and licence are reported anyway.
     */
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoSameSiteCookieRequired]
    #[NoTwoFactorRequired]
    public function getMetadata(): JSONResponse
    {
        $provider = $this->settings->getSelectedImageProvider();
        $metadata = $provider->getMetadata();

        $response = new JSONResponse([
            'url' => $metadata->getImageUrl(),
            'author' => $metadata->getImageAuthor(),
            'attribution' => $metadata->getAttributionUrl(),
            'description' => $metadata->getImageDescription(),
            'source' => $metadata->getSource(),
        ]);
        $response->addHeader('Cache-Control', 'no-store');
        return $response;
    }
}
