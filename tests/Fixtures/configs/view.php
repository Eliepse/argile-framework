<?php

use Eliepse\Argile\Support\Env;

return [

	"viewsPath" => __DIR__ . "/../views/",

	"cache" => [
		"enable" => Env::get("VIEW_CACHE", false),
		"cachePath" => __DIR__ . "/../../cache/framework/views/cache/",
	],

	"compile" => [
		"enable" => Env::get("VIEW_COMPILE", false),
		"cachePath" => __DIR__ . "/../../cache/framework/views/static/",
		"views" => [],
	],

];