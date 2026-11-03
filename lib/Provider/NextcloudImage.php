<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Provider;

use OCA\Splashd\ProviderHandler\Provider;

/**
 * Falls back to the login background from
 * Administration settings > Theming. Nothing is cached, the theming app
 * serves the image itself.
 */
class NextcloudImage extends Provider
{
    public bool $IS_CACHED = false;

    protected function collect(): iterable
    {
        return [];
    }
}
