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
		/** @var ConfigurationManager $manager */
		$manager = Application::getInstance()->resolve(ConfigurationManager::class);

		if (! $manager->has($namespace)) {
			return $default;
		}

		return $manager->get($namespace)->get(join(".", $keys), $default);
	}
}