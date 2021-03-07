<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\Support\Path;
use Psr\Container\ContainerInterface;

final class ConfigurationProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(ConfigurationManager::class, function (ContainerInterface $c) {
			return new ConfigurationManager($this->getConfigurationPath());
		});
	}


	public function boot(): void
	{
		//
	}


	private function getConfigurationPath(): string
	{
		return Path::root("config/");
	}
}