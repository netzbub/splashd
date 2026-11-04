<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Provider;

use OCA\Splashd\ProviderHandler\Provider;

/**
 * The images shipped with the app in img/samples. They are the default so
 * that a fresh installation shows something before a folder is configured.
 */
class BundledImages extends Provider
{
    const SAMPLE_DIR = __DIR__ . '/../../img/samples';

    public int $POOL_LIMIT = 40;

    protected function collect(): iterable
    {
        $dir = realpath(self::SAMPLE_DIR);
        if ($dir === false || !is_dir($dir)) {
            $this->logger->warning('Splashd: no bundled images found at ' . self::SAMPLE_DIR);
            return;
        }

        $files = glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        // More images may be shipped than fit into the pool, so draw a fresh
        // selection on every refill instead of always taking the first ones.
        shuffle($files);

        foreach ($files as $file) {
            $data = @file_get_contents($file);
            if ($data === false) {
                continue;
            }
            yield ['data' => $data, 'meta' => []];
        }
    }
}
