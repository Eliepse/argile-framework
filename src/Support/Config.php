<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Core\Application;

final class Config
{
	static public function get(string $key, mixed $default = null): mixed
	{
		/** @var ConfigRepository $manager */
		$manager = Application::getInstance()->resolve(ConfigRepository::class);
		return $manager->get($key, $default);
	}
}