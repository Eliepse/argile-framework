<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Cache\CacheRepository;
use Eliepse\Argile\Config\ConfigRepository;

final class CacheProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(CacheRepository::class, function (ConfigRepository $configs) {
			return new CacheRepository($configs);
		});

		$this->app->register(Cache::class, function (CacheRepository $repository) {
			return $repository->getStore();
		});
	}
}