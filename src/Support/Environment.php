<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Repositories\EnvironmentRepository;

final class Environment
{
	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public static function get(string $key, mixed $default = null): mixed
	{
		return Application::getInstance()->container->get(EnvironmentRepository::class)->get($key, $default);
	}


	public static function isDevelopment(): bool
	{
		return self::get("APP_ENV") === "local";
	}


	public static function isTesting(): bool
	{
		return self::get("APP_ENV") === "testing";
	}


	public static function isProduction(): bool
	{
		return ! self::isDevelopment() && ! self::isTesting();
	}
}