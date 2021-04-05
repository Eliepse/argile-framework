<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\FilesystemCache;
use Eliepse\Argile\Cache\CacheRepository;
use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Filesystem\StorageRepository;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\Loaders\ViewCacheLoader;
use Eliepse\Argile\View\Loaders\ViewLoader;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\Parsers\GraveurParser;
use Eliepse\Argile\View\ViewFactory;
use Psr\Log\LoggerInterface;

class ViewProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(ViewFactory::class, function (
			StorageRepository $fsRepository,
			ConfigRepository $configs,
			LoggerInterface $logger,
			CacheRepository $cacheRepository,
		) {
			return new ViewFactory(
				new ViewStaticLoader($fsRepository->getDriver("views")),
				new ViewCacheLoader($cacheRepository->getStore($configs->get("views.cache.store"))),
				new ViewLoader($configs->get("view.viewsPath")),
				new GraveurParser(),
				$logger,
				$configs,
			);
		});
	}
}