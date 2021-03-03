<?php

namespace Eliepse\Argile\Tests;

use Eliepse\Argile\App;
use Eliepse\Argile\Testing\LogServiceProvider;
use Eliepse\Argile\Testing\ViewServiceProvider;

class TestCase extends \PHPUnit\Framework\TestCase
{
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$app = App::init(__DIR__);
		$app->boot([
			LogServiceProvider::class,
			ViewServiceProvider::class,
		]);
	}
}