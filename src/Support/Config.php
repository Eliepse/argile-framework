<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\Core\Application;

final class Config
{
	static public function get(string $key, mixed $default = null): mixed
	{
		$keys = explode(".", $key);

		if (count($keys) < 2) {
			return $default;
		}

		$namespace = array_shift($keys);
		$manager = Application::getInstance()->resolve(ConfigurationManager::class);

		return $manager->get($namespace)->get(join(".", $keys), $default);
	}
}