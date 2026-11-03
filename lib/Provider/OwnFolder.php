<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Provider;

use OCA\Splashd\ProviderHandler\Provider;
use OCP\Files\File;
use OCP\Files\Folder;

/**
 * Images from a folder inside this Nextcloud.
 *
 * The folder is addressed by account and path. A team folder is mounted into
 * the file view of every member, so pointing the path at a team folder keeps
 * the image source independent of a single person's own files - as long as
 * the configured account stays a member.
 */
class OwnFolder extends Provider
{
    const CONFIG_ACCOUNT = 'splashd/folder/account';
    const CONFIG_PATH = 'splashd/folder/path';

    /** Mime types we are willing to read. */
    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Skip anything larger, scaling it would cost too much memory. */
    const MAX_SOURCE_BYTES = 30 * 1024 * 1024;

    public function getAccount(): string
    {
        return $this->config->getAppValue($this->appName, self::CONFIG_ACCOUNT, '');
    }

    public function getPath(): string
    {
        return $this->config->getAppValue($this->appName, self::CONFIG_PATH, '');
    }

    public function setAccount(string $account): void
    {
        $this->config->setAppValue($this->appName, self::CONFIG_ACCOUNT, trim($account));
    }

    public function setPath(string $path): void
    {
        $path = trim($path);
        $path = str_replace('\\', '/', $path);
        // No parent traversal, and always relative to the account's files.
        $path = preg_replace('#(^|/)\.\.(/|$)#', '/', $path);
        $this->config->setAppValue($this->appName, self::CONFIG_PATH, trim($path, '/'));
    }

    /**
     * Resolve the configured folder, or null if it is not usable.
     */
    public function resolveFolder(): ?Folder
    {
        $account = $this->getAccount();
        $path = $this->getPath();
        if ($account === '' || $path === '') {
            return null;
        }

        try {
            $userFolder = $this->rootFolder->getUserFolder($account);
            $node = $userFolder->get($path);
        } catch (\Throwable $e) {
            $this->logger->warning('Splashd: folder "' . $path . '" of account "' . $account . '" is not readable: ' . $e->getMessage());
            return null;
        }

        if (!$node instanceof Folder) {
            $this->logger->warning('Splashd: "' . $path . '" is not a folder');
            return null;
        }
        return $node;
    }

    /**
     * How many usable images the configured folder holds. Used by the
     * settings page to tell the admin whether the path is right.
     */
    public function countImages(): int
    {
        $folder = $this->resolveFolder();
        if ($folder === null) {
            return 0;
        }
        $count = 0;
        foreach ($folder->getDirectoryListing() as $node) {
            if ($this->usable($node)) {
                $count++;
            }
        }
        return $count;
    }

    protected function collect(): iterable
    {
        $folder = $this->resolveFolder();
        if ($folder === null) {
            return;
        }

        $nodes = $folder->getDirectoryListing();
        shuffle($nodes);

        foreach ($nodes as $node) {
            if (!$this->usable($node)) {
                continue;
            }
            try {
                $data = $node->getContent();
            } catch (\Throwable $e) {
                continue;
            }
            if (!is_string($data) || $data === '') {
                continue;
            }
            yield ['data' => $data, 'meta' => []];
        }
    }

    private function usable($node): bool
    {
        if (!$node instanceof File) {
            return false;
        }
        if (!in_array(strtolower($node->getMimetype()), self::ALLOWED_MIMES, true)) {
            return false;
        }
        return $node->getSize() > 0 && $node->getSize() <= self::MAX_SOURCE_BYTES;
    }
}
