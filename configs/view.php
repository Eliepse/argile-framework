<?php

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;

return [

	"viewsPath" => Path::resources("views/"),

	"cache" => [
		"enable" => Env::get("VIEW_CACHE", false),
		// TODO(eliepse): replace with a cache driver configuration
		"cachePath" => "framework/views/cache/",
	],

	"compile" => [
		"enable" => Env::get("VIEW_COMPILE", false),
		"driver" => "views",
		"views" => [],
	],

];