<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Support\Env;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\ViewFactory;
use Eliepse\Argile\View\ViewFileSystemLoader;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class ViewServiceProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(ViewFactory::class, function (ContainerInterface $c) {
			$viewCachePath = Env::isProduction() ? $this->getCacheDirectory() : null;
			$filesystemLoader = new ViewFileSystemLoader($this->getViewDirectory(), $viewCachePath);
			$filesystemLoader->setLogger($c->get(LoggerInterface::class));
			$engine = new PhpEngine(new TemplateNameParser(), $filesystemLoader);
			return new ViewFactory($engine);
		});
	}


	protected function getCacheDirectory(): string
	{
		return Path::storage("framework/views/");
	}


	protected function getViewDirectory(): string
	{
		return Path::resources("views/%name%");
	}


	public function boot(): void { }
}