<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\Loaders\ViewCacheLoader;
use Eliepse\Argile\View\Loaders\ViewLoader;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\Parsers\GraveurParser;
use Eliepse\Argile\View\ViewFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ViewProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(ViewFactory::class, function (ContainerInterface $c) {
			$fileSystem = $c->get(Filesystem::class);
			
			return new ViewFactory(
				new ViewStaticLoader($fileSystem, $this->getStaticDirectory()),
				new ViewCacheLoader($fileSystem, $this->getCacheDirectory()),
				new ViewLoader($this->getViewDirectory()),
				new GraveurParser(),
				$c->get(LoggerInterface::class)
			);
		});
	}


	protected function getStaticDirectory(): string
	{
		return Path::storage("framework/views/static/");
	}


	protected function getCacheDirectory(): string
	{
		return Path::storage("framework/views/cache/");
	}


	protected function getViewDirectory(): string
	{
		return Path::resources("views/");
	}


	public function boot(): void { }
}