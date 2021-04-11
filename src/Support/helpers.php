<?php
/** @noinspection PhpUnhandledExceptionInspection */

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Support\Asset;
use Eliepse\Argile\Support\Env;

if (! function_exists("env")) {
	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	function env(string $key, $default = null): mixed
	{
		return Env::get($key, $default);
	}
}

if (! function_exists('app')) {
	/**
	 * @param string|null $service_name
	 *
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	function app(string $service_name = null): mixed
	{
		if (is_string($service_name)) {
			return Application::getInstance()->resolve($service_name);
		}

		return Application::getInstance();
	}
}

if (! function_exists('webpack')) {
	/**
	 * @param string $asset_path
	 * @param string|null $default
	 *
	 * @return string
	 * @throws ErrorException
	 */
	function webpack(string $asset_path, ?string $default = null): string
	{
		return Asset::webpack($asset_path, $default);
	}
}