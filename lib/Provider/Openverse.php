<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

namespace OCA\Splashd\Provider;

use OCA\Splashd\ProviderHandler\Provider;
use OCA\Splashd\ProviderHandler\ProviderMetadata;

/**
 * Images from Openverse, restricted to CC0 and Public Domain Mark.
 *
 * Only the server talks to Openverse. The images are downloaded, scaled and
 * served from the app data, so no request from a visitor's browser ever
 * reaches a third party. CC0 and PDM carry no attribution duty; the creator
 * and the licence are stored and shown anyway.
 *
 * The API needs no key. Anonymous use is limited to 20 requests per minute
 * and 200 per day, which one daily refill is far below.
 */
class Openverse extends Provider
{
    const API = 'https://api.openverse.org/v1/images/';

    /** Only licences that need no attribution. */
    const LICENSES = 'cc0,pdm';

    public string $DEFAULT_SEARCH = 'landscape,mountains,forest';
    public bool $ALLOW_CUSTOMIZING = true;
    public string $DEFAULT_METADATA_URL = 'https://openverse.org';
    public int $POOL_LIMIT = 20;

    public function getWhitelistResourceUrls(): array
    {
        // Nothing to allow: the browser only ever sees our own app data.
        return [];
    }

    public function getMetadata(): ProviderMetadata
    {
        return new ProviderMetadata(
            self::LICENSES === '' ? '' : 'https://creativecommons.org/publicdomain/zero/1.0/',
            $this->DEFAULT_METADATA_URL,
            '',
            '',
            'Openverse (CC0 / Public Domain)'
        );
    }

    protected function collect(): iterable
    {
        $term = $this->getRandomSearchTerm();
        if ($term === '') {
            $term = 'landscape';
        }

        $query = http_build_query([
            'q' => $term,
            'license' => self::LICENSES,
            'aspect_ratio' => 'wide',
            'size' => 'large',
            'extension' => 'jpg',
            'page_size' => $this->POOL_LIMIT,
        ]);

        $response = $this->getData(self::API . '?' . $query);
        if ($response === false) {
            return;
        }

        $json = json_decode($response, true);
        $results = is_array($json['results'] ?? null) ? $json['results'] : [];
        if ($results === []) {
            $this->logger->warning('Splashd: Openverse returned no result for "' . $term . '"');
            return;
        }

        foreach ($results as $result) {
            $url = $result['url'] ?? '';
            if (!is_string($url) || !str_starts_with($url, 'https://')) {
                continue;
            }
            $data = $this->getData($url);
            if ($data === false || $data === '') {
                continue;
            }
            yield [
                'data' => $data,
                'meta' => [
                    'title' => (string)($result['title'] ?? ''),
                    'creator' => (string)($result['creator'] ?? ''),
                    'license' => strtoupper((string)($result['license'] ?? '')),
                    'license_url' => (string)($result['license_url'] ?? ''),
                    'source_url' => (string)($result['foreign_landing_url'] ?? ''),
                ],
            ];
        }
    }
}
