<?php

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;

return [

	"viewsPath" => Path::resources("views/"),

	"cache" => [
		"enable" => Env::get("VIEW_CACHE", false),
		"cachePath" => Path::storage("framework/views/cache/"),
	],

	"compile" => [
		"enable" => Env::get("VIEW_COMPILE", false),
		"cachePath" => Path::storage("framework/views/static/"),
		"views" => [],
	],

];