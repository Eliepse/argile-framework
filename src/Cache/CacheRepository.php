<?php

namespace Eliepse\Argile\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Eliepse\Argile\Config\ConfigRepository;

final class CacheRepository
{
	private array $stores = [];
	private string $default;


	public function __construct(private ConfigRepository $configs)
	{
		$this->default = $this->configs->get("cache.default");
	}


	private function makeStoreWithConfigs(string $name, array $configs = []): CacheProvider
	{
		if (empty($configs) || empty($configs["driver"])) {
			throw new \InvalidArgumentException("Invalid configuration for cache store '$name'.");
		}

		$driver = $configs["driver"];

		return $this->stores[$name] = match ($driver) {
			"filesystem" => new FilesystemDriver($name, $configs),
			default => throw new \ErrorException("The driver '{$driver}' is not supported."),
		};
	}


	private function makeStore(string $name): CacheProvider
	{
		return $this->makeStoreWithConfigs($name, $this->configs->get("cache.stores.$name"));
	}


	public function getStore(string $name = null): CacheProvider
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