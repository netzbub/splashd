<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Settings;

use OCA\Splashd\Services\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;

/**
 * Class AdminSettings
 *
 * @package OCA\Splashd\Settings
 */
class AdminSettings implements ISettings
{
    /**
     * AdminSection constructor.
     *
     * @param IURLGenerator $urlGenerator
     * @param SettingsService $settings
     */
    public function __construct(
        private IURLGenerator $urlGenerator,
        private SettingsService $settings,
    )
    {
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     */
    public function getForm(): TemplateResponse
    {
        return new TemplateResponse('splashd', 'settings/admin', [
            // Relative, not absolute: behind a reverse proxy an absolute URL can
            // carry a different host or scheme than the page itself, and the
            // browser then treats the save as a foreign request and drops the
            // session cookie with it.
            'saveSettingsUrl' => $this->urlGenerator->linkToRoute('splashd.admin_settings.set'),
            'requestCustomizationUrl' =>
                $this->urlGenerator->linkToRoute(
                    'splashd.admin_settings.getCustomization',
                    ['providername' => $this->settings->getImageProviderName()]
                ),
            'styleLogin' => $this->settings->getServerStyleLoginEnabled(),
            'styleDashboard' => $this->settings->getServerStyleDashboardEnabled(),
            // Dashboard was introduced in NC20; always available in supported range NC31+
            'hasDashboard' => true,
            'availableProvider' => $this->settings->getAllImageProvider(),
            'selectedProvider' => $this->settings->getImageProviderName(),
            'isCustomizable' => $this->settings->getSelectedImageProvider()->isCustomizable(),
            'selectionCustomization' => $this->settings->getImageProviderCustomization(),
            'folderAccount' => $this->settings->getFolderAccount(),
            'folderPath' => $this->settings->getFolderPath(),
            'folderCount' => $this->settings->countFolderImages(),
            'poolCount' => $this->settings->getPoolCount(),
            'changeMode' => $this->settings->getChangeMode(),
            'styleTint' => $this->settings->isTintEnabled(),
            'styleStrengthColor' => $this->settings->getColorStrength(),
            'styleStrengthBlur' => $this->settings->getBlurStrength(),
            'styleHighVisibility' => $this->settings->isHighVisibilityLogin(),
            'isCached' => $this->settings->isCached(),
            'imageURL' => $this->settings->getSelectedImageProvider()->getCachedImageURL()
        ]);
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     */
    public function getSection(): string
    {
        return 'theming';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     */
    public function getPriority(): int
    {
        return 9;
    }
}
