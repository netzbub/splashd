<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Services;

use OCA\Splashd\ProviderHandler\Provider;
use OCA\Splashd\ProviderHandler\ProviderDefinitions;
use OCP\Defaults;
use OCA\Splashd\Provider\OwnFolder;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Class SettingsService
 *
 * @package OCA\Splashd\Services
 */
class SettingsService
{

    const STYLE_LOGIN = 'splashd/style/login';
    const STYLE_LOGIN_HIGH_VISIBILITY = 'splashd/style/login/highvisibility';
    const STYLE_DASHBORAD = 'splashd/style/dashborad';
    const USER_STYLE_DASHBORAD = 'splashd/style/dashborad';
    const PROVIDER_SELECTED = 'splashd/provider/selected';
    const PROVIDER_DEFAULT = 'BundledImages';

    const CHANGE_MODE = 'splashd/change/mode';
    const CHANGE_MODE_DEFAULT = 'request';

    const POOL_LAST_ATTEMPT = 'splashd/pool/lastattempt';

    /** Shortest gap between two refills triggered from the public page. */
    const POOL_RETRY_SECONDS = 600;

    const STYLE_TINT_ALLOWED = 'splashd/style/tint';
    const STYLE_STRENGHT_COLOR = 'splashd/style/strength/color';
    const STYLE_STRENGHT_BLUR = 'splashd/style/strength/blur';

    const STYLE_TINT_ALLOWED_DEFAULT = 0; //equals 30%
    const STYLE_STRENGHT_COLOR_DEFAULT = 30; //equals 30%
    const STYLE_STRENGHT_BLUR_DEFAULT = 0;

    const STYLE_LOGIN_HIGH_VISIBILITY_DEFAULT = 0;

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var ProviderDefinitions
     */
    protected $providerDefinitions;

    /**
     * @var \OCP\Defaults
     */
    private $defaults;

    /**
     * FaviconService constructor.
     *
     * @param string|null $userId
     * @param             $appName
     * @param IConfig $config
     * @param IAppData $appData
     * @param Defaults $defaults
     * @param LoggerInterface $logger
     * @param IClientService $clientService
     */
    public function __construct($userId, $appName, IConfig $config, IAppData $appData, Defaults $defaults, LoggerInterface $logger, IClientService $clientService, IRootFolder $rootFolder)
    {
        $this->config = $config;
        $this->userId = $userId;
        if ($this->config->getSystemValue('maintenance', false)) {
            $this->userId = null;
        }
        $this->appName = $appName;

        $this->providerDefinitions = new ProviderDefinitions($this->appName, $logger, $this->config, $appData, $clientService, $rootFolder);
        $this->defaults = $defaults;
    }

    /**
     * If the dashboard should be styled for this user
     *
     * @return bool
     */
    public function getUserStyleDashboardEnabled(): bool
    {
        // for magic value see: https://github.com/nextcloud/server/blame/master/apps/theming/lib/Service/BackgroundService.php
        $themingAppDashboard = $this->config->getUserValue($this->userId, "theming", "background_image", 'default');

        // dont add custom css when custom image was selected
        if ($themingAppDashboard == 'default') {
            return $this->getServerStyleDashboardEnabled();
        }
        return false;
    }

    /**
     * If the page dashboard be styled by default
     *
     * @return bool
     */
    public function getServerStyleDashboardEnabled(): bool
    {
        return $this->config->getAppValue($this->appName, self::STYLE_DASHBORAD, 0) == 1;
    }

    /**
     * Set if the dashboard should be styled by default
     *
     * @param int $styleDashboard
     */
    public function setServerStyleDashboardEnabled(int $styleDashboard = 1)
    {
        $this->config->setAppValue($this->appName, self::STYLE_DASHBORAD, $styleDashboard);
    }

    /**
     * If the login page should be styled by default
     *
     * @return bool
     */
    public function getServerStyleLoginEnabled(): bool
    {
        return $this->config->getAppValue($this->appName, self::STYLE_LOGIN, 1) == 1;
    }

    /**
     * Set if the login page should be styled by default
     *
     * @param int $styleLogin
     */
    public function setServerStyleLoginEnabled(int $styleLogin = 1)
    {
        $this->config->setAppValue($this->appName, self::STYLE_LOGIN, $styleLogin);
    }

    /**
     * Set the selected imageprovider, but does not use the provided string.
     *
     * @param string $providername
     */
    public function setImageProviderSanitized(string $providername): void
    {
        if (!in_array($providername, $this->getAllImageProvider(), true)) {
            return;
        }
        $this->config->setAppValue($this->appName, self::PROVIDER_SELECTED, $providername);
        // Make sure the new provider has images before the next login.
        if ($this->getPoolCount() === 0) {
            $this->refillPool();
        }
    }

    /**
     * Get the selected imageprovider
     *
     * @return string name of the provider
     * @return string current provider
     */
    public function getImageProvider($name): Provider
    {
        return $this->providerDefinitions->getProviderByName($name);
    }

    /**
     * Get the selected imageprovider
     *
     * @return Provider current provider
     */
    public function getSelectedImageProvider(): Provider
    {
        $name = $this->getImageProviderName();
        return $this->providerDefinitions->getProviderByName($name);
    }

    /**
     * Get the selected imageprovider's name
     *
     * @return string current provider name
     */
    public function getImageProviderName(): string
    {
        return $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
    }

    /**
     * Get the selected imageprovider customization
     *
     * @return string current provider customization
     */
    public function getImageProviderCustomization()
    {
        $providername = $this->getImageProviderName();
        $provider = $this->providerDefinitions->getProviderByName($providername);
        return $provider->getCustomSearchterms();
    }

    /**
     * Set the selected imageprovider customization
     *
     * @param string $customization
     */
    public function setImageProviderCustomization($customization)
    {
        $providername = $this->getImageProviderName();
        $provider = $this->providerDefinitions->getProviderByName($providername);
        $provider->setCustomSearchTerms($customization);
        $this->refillPool();
    }

    /**
     * Get all defined imageprovider
     * @return array[String]
     */
    public function getAllImageProvider(): array
    {
        return $this->providerDefinitions->getAllProviderNames();
    }

    /**
     * Get all defined imageprovider that allow customization
     */
    public function getAllCustomizableImageProvider()
    {
        $all = [];
        foreach ($this->providerDefinitions->getAllProviderNames() as $value) {
            $provider = $this->providerDefinitions->getProviderByName($value);
            if ($provider->isCustomizable()) {
                $all[] = $value;
            }
        }
        return $this->providerDefinitions->getAllProviderNames();
    }


    /**
     * Returns the URL to the custom Unsplash-path
     *
     * @return String
     */
    public function headerbackgroundLink($size)
    {
        $providerName = $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
        $provider = $this->providerDefinitions->getProviderByName($providerName);

        if ($provider->isCached()) {
            return $provider->getCachedImageURL();
        }
        return $provider->getRandomImageUrl($size);
    }

    /**
     * Get all URLs for whitelisting
     */
    public function getWhitelistingUrlsForSelectedProvider()
    {
        $providerName = $this->config->getAppValue($this->appName, self::PROVIDER_SELECTED, self::PROVIDER_DEFAULT);
        $provider = $this->providerDefinitions->getProviderByName($providerName);
        return $provider->getWhitelistResourceUrls();
    }

    /**
     * nextcloud theming main color
     *
     * @param String $styleUrl
     */
    public function getInstanceColor()
    {
        return $this->config->getAppValue('theming', 'color', $this->defaults->getColorPrimary());
    }

    /**
     * If the login page should be styled by default
     *
     * @return bool
     */
    public function isTintEnabled(): bool
    {
        return $this->config->getAppValue($this->appName, self::STYLE_TINT_ALLOWED, self::STYLE_TINT_ALLOWED_DEFAULT);
    }

    public function setTint(int $tinting): void
    {
        $this->config->setAppValue($this->appName, self::STYLE_TINT_ALLOWED, $tinting);
    }


    /**
     * color strength
     *
     * @param String $styleUrl
     */
    public function getColorStrength()
    {
        return $this->config->getAppValue($this->appName, self::STYLE_STRENGHT_COLOR, self::STYLE_STRENGHT_COLOR_DEFAULT);
    }

    /**
     * set color strength
     *
     * @param String $styleUrl
     */
    public function setColorStrength(int $strength)
    {
        if ($strength > 100) {
            $strength = 100;
        }
        if ($strength < 0) {
            $strength = 0;
        }
        $this->config->setAppValue($this->appName, self::STYLE_STRENGHT_COLOR, $strength);
    }


    /**
     * blur strength
     *
     * @param String $styleUrl
     */
    public function getBlurStrength()
    {
        return $this->config->getAppValue($this->appName, self::STYLE_STRENGHT_BLUR, self::STYLE_STRENGHT_BLUR_DEFAULT);
    }

    /**
     * set blur strength
     *
     * @param String $styleUrl
     */
    public function setBlurStrength(int $strength)
    {
        if ($strength > 25) {
            $strength = 25;
        }
        if ($strength < 0) {
            $strength = 0;
        }
        $this->config->setAppValue($this->appName, self::STYLE_STRENGHT_BLUR, $strength);
    }

    /**
     * If the login page should be styled as High Visibility for Legal reasons
     *
     * @return bool
     */
    public function isHighVisibilityLogin(): bool
    {
        return $this->config->getAppValue($this->appName, self::STYLE_LOGIN_HIGH_VISIBILITY, self::STYLE_LOGIN_HIGH_VISIBILITY_DEFAULT);
    }

    public function setHighVisibilityLogin(int $highVisibility): void
    {
        $this->config->setAppValue($this->appName, self::STYLE_LOGIN_HIGH_VISIBILITY, $highVisibility);
    }

    /**
     * How often the background changes: on every request or once a day.
     */
    public function getChangeMode(): string
    {
        $mode = $this->config->getAppValue($this->appName, self::CHANGE_MODE, self::CHANGE_MODE_DEFAULT);
        return $mode === 'daily' ? 'daily' : 'request';
    }

    public function setChangeMode(string $mode): void
    {
        $this->config->setAppValue($this->appName, self::CHANGE_MODE, $mode === 'daily' ? 'daily' : 'request');
    }

    /**
     * The account whose file view the image folder is read through.
     */
    public function getFolderAccount(): string
    {
        return $this->getOwnFolderProvider()->getAccount();
    }

    public function setFolderAccount(string $account): void
    {
        $this->getOwnFolderProvider()->setAccount($account);
        $this->refillPool('OwnFolder');
    }

    /**
     * The path of the image folder, relative to that account's files.
     */
    public function getFolderPath(): string
    {
        return $this->getOwnFolderProvider()->getPath();
    }

    public function setFolderPath(string $path): void
    {
        $this->getOwnFolderProvider()->setPath($path);
        $this->refillPool('OwnFolder');
    }

    /**
     * Number of usable images in the configured folder, for the settings page.
     */
    public function countFolderImages(): int
    {
        return $this->getOwnFolderProvider()->countImages();
    }

    private function getOwnFolderProvider(): OwnFolder
    {
        $provider = $this->providerDefinitions->getProviderByName('OwnFolder');
        if (!$provider instanceof OwnFolder) {
            throw new \RuntimeException('Splashd: the OwnFolder provider is missing');
        }
        return $provider;
    }

    /**
     * Refill the pool of the given provider, or of the selected one.
     *
     * @return int number of images in the pool afterwards
     */
    public function refillPool(?string $providerName = null): int
    {
        $provider = $this->providerDefinitions->getProviderByName($providerName ?? $this->getImageProviderName());
        if (!$provider->isCached()) {
            return 0;
        }
        return $provider->fillPool();
    }

    /**
     * Refill, but not more often than every POOL_RETRY_SECONDS.
     *
     * The image endpoint is public, so an empty pool must not let every
     * anonymous request start a download run.
     *
     * @return int number of images in the pool afterwards
     */
    public function refillPoolThrottled(): int
    {
        $last = (int)$this->config->getAppValue($this->appName, self::POOL_LAST_ATTEMPT, '0');
        if ($last > time() - self::POOL_RETRY_SECONDS) {
            return $this->getPoolCount();
        }
        $this->config->setAppValue($this->appName, self::POOL_LAST_ATTEMPT, (string)time());
        return $this->refillPool();
    }

    /**
     * Number of images currently available for the selected provider.
     */
    public function getPoolCount(): int
    {
        $provider = $this->getSelectedImageProvider();
        if (!$provider->isCached()) {
            return 0;
        }
        return count($provider->listPool());
    }

    /**
     * Whether the selected provider serves from the app data.
     */
    public function isCached(): bool
    {
        return $this->getSelectedImageProvider()->isCached();
    }
}
