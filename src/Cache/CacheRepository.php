<?php

namespace Eliepse\Argile\Cache;

use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Config\ConfigRepository;

final class CacheRepository
{
	private array $stores = [];
	private string $default;


	public function __construct(private ConfigRepository $configs)
	{
		$this->default = $this->configs->get("cache.default");
	}


	private function makeStoreWithConfigs(string $name, array $configs = []): Cache
	{
		if (empty($configs) || empty($configs["driver"])) {
			throw new \InvalidArgumentException("Invalid configuration for cache store '$name'.");
		}

		$driver = match ($configs["driver"]) {
			"filesystem" => new FilesystemDriver($name, $configs),
		};

		return $this->stores[$name] = $driver;
	}


	private function makeStore(string $name): Cache
	{
		return $this->makeStoreWithConfigs($name, $this->configs->get("cache.stores.$name"));
	}


	public function getStore(string $name = null): Cache
	{
		if (null === $name) {
			return $this->getStore($this->default);
		}

		if (empty($this->stores[$name])) {
			return $this->makeStore($name);
		}

		return $this->stores[$name];
	}
}