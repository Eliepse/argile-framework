<?php

namespace Eliepse\Argile\Tests;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Testing\EnvironmentProvider;
use Eliepse\Argile\Testing\LogProvider;
use Eliepse\Argile\Testing\ViewProvider;

class TestCase extends \PHPUnit\Framework\TestCase
{
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$app = Application::init(__DIR__);
		$app->boot([
			EnvironmentProvider::class,
			LogProvider::class,
			ViewProvider::class,
		]);
	}
}