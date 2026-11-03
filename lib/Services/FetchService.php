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
     * Refill the pool of the currently selected provider.
     *
     * @return int number of images in the pool afterwards
     */
    public function fetch(): int
    {
        return $this->settings->refillPool();
    }

}
