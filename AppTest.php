<?php

namespace Eliepse\Argile\Tests;

use Eliepse\Argile\App;
use Eliepse\Argile\Testing\AppServiceProvider;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
	public function testAppBoot(): void
	{
		$app = App::init(__DIR__);
		$app->boot([
			AppServiceProvider::class,
		]);
		$this->assertInstanceOf(\Slim\App::class, $app->getSlim());
		$this->assertInstanceOf(AppServiceProvider::class, $app->container->get(AppServiceProvider::class));
	}
}
