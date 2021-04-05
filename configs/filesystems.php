<?php

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;

return [
	"storage" => [
		"path" => Path::storage(),
	],

	"views" => [
		"path" => Path::storage("framework/views/"),
	],
];