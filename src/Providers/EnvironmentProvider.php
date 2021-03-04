<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Core\Environment;
use Eliepse\Argile\Core\EnvironmentInterface;

class EnvironmentProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->register(EnvironmentInterface::class, function () {
			return $this->getRepository();
		});
	}


	public function boot(): void
	{
		//
	}


	protected function getRepository(): EnvironmentInterface
	{
		return Environment::createFromFile($this->app->getProjectDirectory());
	}
}