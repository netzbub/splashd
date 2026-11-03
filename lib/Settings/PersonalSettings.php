<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Settings;

use OCA\Splashd\Services\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Defaults;
use OCP\Settings\ISettings;

/**
 * Class PersonalSettings
 *
 * @package OCA\Splashd\Controller\Settings
 */
class PersonalSettings implements ISettings
{

    /**
     * PersonalSection constructor.
     *
     * @param SettingsService $settings
     * @param Defaults $theming
     */
    public function __construct(
        private SettingsService $settings,
        private Defaults $theming,
    )
    {
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     */
    public function getForm(): TemplateResponse
    {

        $dashboard = $this->settings->getServerStyleDashboardEnabled();
        $login = $this->settings->getServerStyleLoginEnabled();

        if ($this->settings->getImageProviderName() == "Nextcloud Image") {
            $dashboard = false;
            $login = false;
        }

        return new TemplateResponse('splashd', 'settings/personal', [
            'selectedProvider' => str_replace(' ', '', $this->settings->getImageProviderName()),
            'label' => $this->theming->getEntity(),
            'dashboard' => $dashboard,
            'login' => $login,
        ], TemplateResponse::RENDER_AS_BLANK);
    }

    /**
     * @return string
     */
    public function getSection(): string
    {
        return "theming";
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 75;
    }
}
