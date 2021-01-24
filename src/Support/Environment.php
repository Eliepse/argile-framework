<?php

namespace Eliepse\Argile\Support;

use Dotenv\Dotenv;
use ErrorException;

final class Environment
{
	public static function load(string $env_dir): void
	{
		$dotenv = Dotenv::createImmutable($env_dir);
		$dotenv->load();
		$dotenv->required('APP_ENV')->notEmpty()->allowedValues(['local', 'production']);
		$dotenv->required('APP_ONLINE')->isBoolean();
		$dotenv->ifPresent('APP_SESSION_PREFIX')->notEmpty();
		$dotenv->ifPresent('APP_CACHE_PREFIX')->notEmpty();
	}


	/**
	 * @param string $key
	 * @param array<string, mixed> $rules
	 * @param bool $throw
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	public static function validate(string $key, array $rules, bool $throw = true): bool
	{
		if (isset($rules['required']) && true === $rules['required'] && ! isset($_ENV[$key])) {
			if ($throw) {
				throw new ErrorException("The '$key' environment variable is required.");
			}
			return false;
		}

		$value = self::get($key);

		if (isset($rules['empty']) && ! $rules['empty'] && empty($value)) {
			if ($throw) {
				throw new ErrorException("The '$key' environment cannot be empty.");
			}
			return false;
		}

		if (isset($rules['in']) && ! in_array($value, $rules['in'])) {
			if ($throw) {
				throw new ErrorException("The '$key' environment must be one of: " . join(', ', $rules['in']));
			}
			return false;
		}

		if (isset($rules['type'])) {
			if ('integer' === $rules['type'] && ! ctype_digit(strval($value))) {
				if ($throw) {
					throw new ErrorException("The '$key' environment must be an integer.");
				}
				return false;
			}

			if ('boolean' === $rules['type'] && (! is_bool($value) || in_array($value, ["true", "false"]))) {
				if ($throw) {
					throw new ErrorException("The '$key' environment must be a boolean.");
				}
				return false;
			}
		}

		return true;
	}


	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed|null
	 */
	public static function get(string $key, $default = null)
	{
		if (! isset($_ENV[$key])) {
			return $default;
		}

		$value = $_ENV[$key];

		if (false === $value) {
			return $default;
		}

		switch (strtolower($value)) {
			case 'true':
				return true;
			case 'false':
				return false;
			case 'null':
				return null;
		}

		return $value;
	}


	public static function isDevelopment(): bool
	{
		return self::get("APP_ENV") === "local";
	}


	public static function isProduction(): bool
	{
		return ! self::isDevelopment();
	}
}