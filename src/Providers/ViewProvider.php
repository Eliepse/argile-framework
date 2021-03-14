<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\FilesystemCache;
use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\View\Loaders\ViewCacheLoader;
use Eliepse\Argile\View\Loaders\ViewLoader;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\Parsers\GraveurParser;
use Eliepse\Argile\View\ViewFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ViewProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(ViewFactory::class, function (
			Filesystem $fs,
			ConfigurationManager $configs,
			LoggerInterface $logger
		) {
			$viewConfig = $configs->get("view");

			return new ViewFactory(
				new ViewStaticLoader($fs, $viewConfig->get("compile.cachePath")),
				new ViewCacheLoader(new FilesystemCache($viewConfig->get("cache.cachePath"))),
				new ViewLoader($viewConfig->get("viewsPath")),
				new GraveurParser(),
				$logger,
				$configs,
			);
		});
	}
}