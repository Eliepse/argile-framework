<?php

namespace Eliepse\Argile\Testing;

final class LogServiceProvider extends \Eliepse\Argile\Providers\LogServiceProvider
{
	protected function getLogDirectory(): string
	{
		return __DIR__ . "/../../tests/logs/log.log";
	}
}