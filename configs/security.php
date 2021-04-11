<?php

use Eliepse\Argile\Support\Env;

return [
	// Content Security Policy
	"csp" => [
		"reportOnly" => Env::isDevelopment(),
		"defaultSrc" => "'self'",
		"directives" => [
			"style-src" => "'self' 'unsafe-inline'",
			"script-src" => "'self' 'unsafe-inline'",
		],
	],
];