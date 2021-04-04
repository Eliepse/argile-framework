<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Filesystem\StorageRepository;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

final class FilesystemProvider extends ServiceProvider
{

	public function register(): void
	{
		$this->app->register(StorageRepository::class, function (ContainerInterface $c) {
			$configs = $c->get(ConfigRepository::class)->get("filesystems");
			return new StorageRepository($configs);
		});

		$this->app->register(Filesystem::class, function (ContainerInterface $c) {
			return $c->get(StorageRepository::class)->getDriver();
		});
	}
}