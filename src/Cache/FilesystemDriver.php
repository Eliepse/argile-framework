<?php

namespace Eliepse\Argile\Cache;

use Doctrine\Common\Cache\FilesystemCache;

final class FilesystemDriver extends FilesystemCache implements CacheDriver
{
	public function __construct(private string $name, array $config = [])
	{
		parent::__construct(
			$config["path"],
			$config["extension"] ?? self::EXTENSION,
			$config["umask"] ?? 0002,
		);
	}
}