<?php

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Providers\CacheProvider;
use Eliepse\Argile\Providers\FilesystemProvider;
use Eliepse\Argile\Providers\ViewProvider;

return [

	"session" => [
		"name" => "argile_session",
        "secure" => Application::getInstance()->isProduction(),
		"lifetime" => 3_600 * 24 * 14, // 14 days
	],

	"providers" => [
		FilesystemProvider::class,
		CacheProvider::class,
		ViewProvider::class,
	],

];