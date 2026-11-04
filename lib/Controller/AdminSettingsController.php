<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Controller;

use OCA\Splashd\Services\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Class AdminSettingsController
 *
 * @package OCA\Splashd\Controller
 */
class AdminSettingsController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private SettingsService $settings,
    )
    {
        parent::__construct($appName, $request);
    }

    /**
     * Update one admin setting.
     */
    public function set(string $key, bool|string|int|array|null $value): JSONResponse
    {
        if (is_string($value) && strtolower($value) === 'true') {
            $value = true;
        }
        if (is_string($value) && strtolower($value) === 'false') {
            $value = false;
        }

        switch ($key) {
            case 'style/login':
                $this->settings->setServerStyleLoginEnabled($value ? 1 : 0);
                break;
            case 'style/dashboard':
                $this->settings->setServerStyleDashboardEnabled($value ? 1 : 0);
                break;
            case 'provider/provider':
                $this->settings->setImageProviderSanitized((string)$value);
                return $this->generateProviderResponse((string)$value);
            case 'provider/customization':
                $this->settings->setImageProviderCustomization((string)$value);
                return $this->generateProviderResponse((string)$value);
            case 'folder/account':
                $this->settings->setFolderAccount((string)$value);
                return $this->generateProviderResponse((string)$value);
            case 'folder/path':
                $this->settings->setFolderPath((string)$value);
                return $this->generateProviderResponse((string)$value);
            case 'change/mode':
                $this->settings->setChangeMode((string)$value);
                break;
            case 'style/tint':
                $this->settings->setTint($value ? 1 : 0);
                break;
            case 'style/strength/color':
                $this->settings->setColorStrength((int)filter_var($value, FILTER_SANITIZE_NUMBER_INT));
                break;
            case 'style/strength/blur':
                $this->settings->setBlurStrength((int)filter_var($value, FILTER_SANITIZE_NUMBER_INT));
                break;
            case 'style/login/highvisibility':
                $this->settings->setHighVisibilityLogin($value ? 1 : 0);
                break;
            case 'pool/refill':
                $this->settings->refillPool();
                return $this->generateProviderResponse('success');
            default:
                return new JSONResponse(['status' => 'error'], Http::STATUS_BAD_REQUEST);
        }

        // Report the value as it now stands in the configuration, so that the
        // settings page shows what was really stored and not what was sent.
        return new JSONResponse([
            'status' => 'ok',
            'key' => $key,
            'stored' => $this->readBack($key),
        ]);
    }

    /**
     * Read one setting back after it was written.
     *
     * @return bool|int|string|null null when the key has no simple value
     */
    private function readBack(string $key): bool|int|string|null
    {
        return match ($key) {
            'style/login' => $this->settings->getServerStyleLoginEnabled(),
            'style/dashboard' => $this->settings->getServerStyleDashboardEnabled(),
            'change/mode' => $this->settings->getChangeMode(),
            'style/tint' => $this->settings->isTintEnabled(),
            'style/strength/color' => (int)$this->settings->getColorStrength(),
            'style/strength/blur' => (int)$this->settings->getBlurStrength(),
            'style/login/highvisibility' => $this->settings->isHighVisibilityLogin(),
            default => null,
        };
    }

    /**
     * Search terms stored for the given provider.
     */
    public function getCustomization(string $providername): JSONResponse
    {
        if (!in_array($providername, $this->settings->getAllImageProvider(), true)) {
            return new JSONResponse(['status' => 'error', 'message' => 'Unknown provider'], Http::STATUS_BAD_REQUEST);
        }
        $provider = $this->settings->getImageProvider($providername);
        return new JSONResponse(['status' => 'ok', 'customization' => $provider->getCustomSearchterms()]);
    }

    /**
     * State the settings page needs after a change: which fields apply to the
     * selected provider, how many images are ready, and a fresh preview.
     */
    private function generateProviderResponse(string $value): JSONResponse
    {
        $provider = $this->settings->getSelectedImageProvider();
        $url = $provider->getCachedImageURL();

        return new JSONResponse([
            'status' => $value,
            'provider' => $provider->getName(),
            'isCached' => $provider->isCached(),
            'isCustomizable' => $provider->isCustomizable(),
            'poolCount' => $this->settings->getPoolCount(),
            'folderCount' => $this->settings->countFolderImages(),
            // Cache buster so that the preview really reloads.
            'imageURL' => $url . (str_contains($url, '?') ? '&' : '?') . 'p=' . random_int(1000, 999999),
        ]);
    }
}
