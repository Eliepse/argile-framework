<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Filesystem\StorageRepository;
use League\Flysystem\Filesystem;

final class Storage
{
	private static StorageRepository $_repository;


	private static function repository(): StorageRepository
	{
		if (self::$_repository) {
			return self::$_repository;
		}

		return self::$_repository = Application::getInstance()->resolve(StorageRepository::class);
	}


	public static function driver(string $name): ?Filesystem
	{
		return self::repository()->getDriver($name);
	}
}