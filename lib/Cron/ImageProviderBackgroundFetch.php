<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Cron;

use OCA\Splashd\Services\FetchService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

class ImageProviderBackgroundFetch extends TimedJob
{
    public function __construct(
        ITimeFactory $time,
        private FetchService $fetchService,
        private LoggerInterface $logger,
    )
    {
        parent::__construct($time);
        // Run once a day
        $this->setInterval(24 * 3600);
    }

    protected function run($arguments)
    {
        $count = $this->fetchService->fetch();
        $this->logger->info('Splashd: daily refill done, ' . $count . ' image(s) in the pool');
    }

}
