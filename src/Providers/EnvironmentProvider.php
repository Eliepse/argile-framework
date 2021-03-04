<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Repositories\EnvironmentRepository;

class EnvironmentProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->register(EnvironmentRepository::class, function () {
			return $this->getRepository();
		});
	}


	public function boot(): void
	{
		//
	}


	protected function getRepository(): EnvironmentRepository
	{
		return EnvironmentRepository::createFromFile($this->app->getProjectDirectory());
	}
}