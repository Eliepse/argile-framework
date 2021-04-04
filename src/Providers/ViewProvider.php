<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\FilesystemCache;
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
			LoggerInterface $logger
		) {
			return new ViewFactory(
				new ViewStaticLoader($fsRepository->getDriver("storage"), $configs->get("view.compile.cachePath")),
				new ViewCacheLoader(new FilesystemCache(Path::storage($configs->get("view.cache.cachePath")))),
				new ViewLoader($configs->get("view.viewsPath")),
				new GraveurParser(),
				$logger,
				$configs,
			);
		});
	}
}