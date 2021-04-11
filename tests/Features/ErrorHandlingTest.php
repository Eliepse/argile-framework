<?php

namespace Eliepse\Argile\Tests\Features;

use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Tests\Fixtures\Controllers\GenerateErrorTestController;
use Eliepse\Argile\Tests\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class ErrorHandlingTest extends TestCase
{
	public function testItCachesErrors(): void
	{
		$route = Router::get("/broken", GenerateErrorTestController::class);

		$this->markTestIncomplete("Cannot make slim router work as expected.");

		$this->app->run();

		$request = (new ServerRequestFactory())->createServerRequest("GET", "http://localhost:8080" . $route->getPattern());
		$response = $this->app->getSlim()->handle($request);
	}
}
