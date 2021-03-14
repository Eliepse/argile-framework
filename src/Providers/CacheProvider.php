<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Eliepse\Argile\Support\Path;

final class CacheProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(Cache::class, function () {
			// TODO: allow to configure global cache with configurations
			return new FilesystemCache(Path::storage("framework/cache/"));
		});
	}
}