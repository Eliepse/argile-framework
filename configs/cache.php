<?php

use Eliepse\Argile\Support\Path;

return [
	"default" => "file",

	"stores" => [
		"file" => [
			"driver" => "filesystem",
			"path" => Path::storage("framework/cache/"),
		],
		"views" => [
			"driver" => "filesystem",
			"path" => Path::storage("framework/views/cache"),
		],
	],
];