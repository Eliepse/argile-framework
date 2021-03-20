<?php

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;

return [

	"viewsPath" => Path::root("Fixtures/views/"),

	"cache" => [
		"enable" => Env::get("VIEW_CACHE", false),
		"cachePath" => Path::root("cache/framework/views/cache/"),
	],

	"compile" => [
		"enable" => Env::get("VIEW_COMPILE", false),
		"cachePath" => Path::root("cache/framework/views/static/"),
		"views" => [],
	],

];