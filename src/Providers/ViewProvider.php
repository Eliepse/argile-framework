<?php

namespace Eliepse\Argile\Providers;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Eliepse\Argile\Support\Path;
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
		$this->app->register(ViewFactory::class, function (Filesystem $fs, LoggerInterface $logger) {
			return new ViewFactory(
				new ViewStaticLoader($fs, $this->getStaticDirectory()),
				new ViewCacheLoader($this->getCache()),
				new ViewLoader($this->getViewDirectory()),
				new GraveurParser(),
				$logger
			);
		});
	}


	protected function getStaticDirectory(): string
	{
		return Path::storage("framework/views/static/");
	}


	protected function getCache(): Cache
	{
		return new FilesystemCache(Path::storage("framework/views/cache/"));
	}


	protected function getViewDirectory(): string
	{
		return Path::resources("views/");
	}
}