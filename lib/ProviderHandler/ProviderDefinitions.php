<?php
/**
 * @copyright Copyright (c) 2018, Felix Nüsse
 *
 * @author Felix Nüsse <felix.nuesse@t-online.de>
 *
 * @license GPL-v3.0
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Modified for the Splashd fork: the remote providers were replaced by the
 * own folder, the bundled images and Openverse.
 */

namespace OCA\Splashd\ProviderHandler;

use OCA\Splashd\Provider\BundledImages;
use OCA\Splashd\Provider\NextcloudImage;
use OCA\Splashd\Provider\Openverse;
use OCA\Splashd\Provider\OwnFolder;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class ProviderDefinitions
{
    /** The provider used when the configured one is unknown or unset. */
    const FALLBACK = 'BundledImages';

    /** @var Provider[] */
    protected array $definitions = [];

    public function __construct(
        protected string $appName,
        protected LoggerInterface $logger,
        protected IConfig $config,
        protected IAppData $appData,
        protected IClientService $clientService,
        protected IRootFolder $rootFolder,
    )
    {
        foreach ([OwnFolder::class, BundledImages::class, Openverse::class, NextcloudImage::class] as $class) {
            $provider = $this->build($class);
            $this->definitions[$provider->getName()] = $provider;
        }
    }

    /**
     * @param class-string<Provider> $class
     */
    private function build(string $class): Provider
    {
        // The short class name doubles as the provider name, the app data
        // folder and the key of its settings.
        $name = substr($class, strrpos($class, '\\') + 1);
        return new $class(
            $this->appName,
            $this->logger,
            $this->config,
            $this->appData,
            $name,
            $this->clientService,
            $this->rootFolder,
        );
    }

    /**
     * The provider with that name, or the fallback.
     */
    public function getProviderByName($name): Provider
    {
        if (isset($this->definitions[$name])) {
            return $this->definitions[$name];
        }
        $this->logger->warning('Splashd: provider "' . $name . '" is unknown, using ' . self::FALLBACK);
        return $this->definitions[self::FALLBACK];
    }

    /**
     * @return string[]
     */
    public function getAllProviderNames(): array
    {
        return array_keys($this->definitions);
    }
}
