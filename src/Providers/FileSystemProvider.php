<?php

namespace Eliepse\Argile\Providers;

use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class FileSystemProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(Filesystem::class, function (ContainerInterface $c) {
			return new Filesystem();
		});
	}
}