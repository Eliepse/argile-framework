<?php

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;

return [

	"viewsPath" => Path::resources("views/"),

	"maintenanceView" => null,

	"cache" => [
		"enable" => Env::get("VIEW_CACHE", false),
		"store" => "views",
	],

	"compile" => [
		"enable" => Env::get("VIEW_COMPILE", false),
		"driver" => "views",
		"views" => [],
	],

];