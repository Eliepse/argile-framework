<?php

namespace Eliepse\Argile\Config;

final class ConfigurationManager
{
	private array $configs = [];


	public function __construct(private string $configPath) { }


	public function get(string $namespace): Configuration
	{
		if (! isset($this->configs[$namespace])) {
			return $this->loadConfiguration($namespace);
		}

		return $this->configs[$namespace];
	}


	private function loadConfiguration(string $namespace): Configuration
	{
		$path = $this->configPath . "$namespace.php";

		if (! is_readable($path)) {
			throw new \ErrorException("Unable to load a configuration file: $namespace");
		}

		/** @noinspection PhpIncludeInspection */
		$configs = include $path;

		if (! is_array($configs)) {
			throw new \ErrorException("Configuration file should return an array for: $namespace");
		}

		$this->set($config = new Configuration($namespace, $configs));

		return $config;
	}


	public function set(Configuration $config): void
	{
		$this->configs[$config->getNamespace()] = $config;
	}


	public function has(string $namespace): bool
	{
		if (isset($this->configs[$namespace])) {
			return true;
		}

		try {
			$this->loadConfiguration($namespace);
			return true;
		} catch (\ErrorException) {
			return false;
		}
	}
}