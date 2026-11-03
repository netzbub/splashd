<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Services;

use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Class FetchService
 *
 * @package OCA\Splashd\Services
 */
class FetchService
{

    /**
     * @var SettingsService
     */
    protected $settings;

    /**
     * FetchService constructor.
     *
     */
    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Fetch the currently selected provider image
     */
    public function fetch(): void
    {
        $provider = $this->settings->getSelectedImageProvider();
        if ($provider->isCached()) {
            $provider->fetchCached();
        }
    }

}
