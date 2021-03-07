<?php

namespace Eliepse\Argile\Config;

final class ConfigurationManager
{
	private array $configs = [];


	public function __construct(private string $configPath) { }


	public function get($namespace): Configuration
	{
		if (! isset($this->configs[$namespace])) {
			$this->loadConfiguration($namespace);
		}

		return $this->configs[$namespace];
	}


	private function loadConfiguration(string $namespace): void
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

		$this->set(new Configuration($namespace, $configs));
	}


	public function set(Configuration $config): void
	{
		$this->configs[$config->getNamespace()] = $config;
	}
}