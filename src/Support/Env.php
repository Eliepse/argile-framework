<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Core\Application;

final class Env
{
	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public static function get(string $key, mixed $default = null): mixed
	{
		return Application::getInstance()->getEnvironment()->get($key, $default);
	}


	public static function isDevelopment(): bool
	{
		return Application::getInstance()->isDevelopment();
	}


	public static function isTesting(): bool
	{
		return Application::getInstance()->isTesting();
	}


	public static function isProduction(): bool
	{
		return Application::getInstance()->isProduction();
	}
}