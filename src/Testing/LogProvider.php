<?php

namespace Eliepse\Argile\Testing;

final class LogProvider extends \Eliepse\Argile\Providers\LogProvider
{
	protected function getLogDirectory(): string
	{
		return __DIR__ . "/../../tests/logs/log.log";
	}
}