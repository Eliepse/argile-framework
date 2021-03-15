<?php

namespace Eliepse\Argile\Config;

use Eliepse\Argile\Support\Arr;

final class ConfigRepository
{
	private array $configs = [];
	private array $unknownNamespaces = [];


	public function __construct(private string $configPath) { }


	public function get(string $key, mixed $default = null): mixed
	{
		$path = explode(".", $key);
		$namespace = array_shift($path);

		if (in_array($namespace, $this->unknownNamespaces, true)) {
			return $default;
		}

		if (! $this->has($namespace)) {
			return $default;
		}

		if (count($path) === 0) {
			return $this->configs[$namespace];
		}

		return Arr::get($this->configs[$namespace], join(".", $path), $default);
	}


	private function loadConfiguration(string $namespace): bool
	{
		$path = $this->configPath . "$namespace.php";

		// Set path to default if not found on project path
		if (! file_exists($path)) {
			$path = __DIR__ . "/../../configs/$namespace.php";
		}

		if (! file_exists($path)) {
			$this->unknownNamespaces[] = $namespace;
			return false;
		}

		/** @noinspection PhpIncludeInspection */
		$configs = include $path;

		if (! is_array($configs)) {
			$this->unknownNamespaces[] = $namespace;
			return false;
		}

		$this->configs[$namespace] = $configs;
		return true;
	}


	/**
	 * @param string $key
	 * @param mixed $value
	 * @param bool $loadNamespace
	 *
	 * @internal
	 */
	public function set(string $key, mixed $value): void
	{
		$this->configs = Arr::set($this->configs, $key, $value);
	}


	public function has(string $namespace): bool
	{
		if (isset($this->configs[$namespace]) || in_array($namespace, $this->unknownNamespaces, true)) {
			return true;
		}

		return $this->loadConfiguration($namespace);
	}
}