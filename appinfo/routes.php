<?php

declare(strict_types=1);

/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

return [
	'routes' => [
		// AdminSettingsController
		[
			'name' => 'admin_settings#set',
			'url' => '/settings/admin/set',
	   		'verb' => 'POST'
		],
		[
			'name' => 'admin_settings#getCustomization',
			'url' => '/settings/admin/getCustomization/{providername}',
			'verb' => 'GET'
		],
		// CssController
		[
			'name' => 'css#login',
			'url' => '/api/login.css',
			'verb' => 'GET'
		],
		[
			'name' => 'css#dashboard',
			'url' => '/api/dashboard.css',
			'verb' => 'GET'
		],
		// ImageController
		[
			'name' => 'image#get',
			'url' => '/api/image',
			'verb' => 'GET'
		],
		[
			'name' => 'image#getMetadata',
			'url' => '/api/metadata',
			'verb' => 'GET'
   		],
	],
];
