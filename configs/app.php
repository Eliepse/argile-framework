<?php

use Eliepse\Argile\Providers\CacheProvider;
use Eliepse\Argile\Providers\FilesystemProvider;
use Eliepse\Argile\Providers\ViewProvider;

return [

	"providers" => [
		FilesystemProvider::class,
		CacheProvider::class,
		ViewProvider::class,
	],

];