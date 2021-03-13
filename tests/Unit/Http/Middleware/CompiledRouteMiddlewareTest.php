<?php

namespace Eliepse\Argile\Tests\Unit\Http\Middleware;

use Eliepse\Argile\Commands\CompileRoutesCommand;
use Eliepse\Argile\Http\Middleware\CompiledRouteMiddleware;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\Fixtures\Controllers\BuildtimeTestController;
use Eliepse\Argile\Tests\Fixtures\Controllers\RuntimeTestController;
use Slim\Psr7\Factory\ServerRequestFactory;

class CompiledRouteMiddlewareTest extends \Eliepse\Argile\Tests\TestCase
{
	public function testDoesNotUseCompiledFile(): void
	{
		$route = $this->app->getSlim()->get("/generated", RuntimeTestController::class)
			->addMiddleware(new CompiledRouteMiddleware());

		$pattern = $route->getPattern();

		$this->execute(CompileRoutesCommand::class);

		$staticFilepath = Path::storage("framework/routes/static/" . hash('sha256', $pattern));

		$factory = new ServerRequestFactory();
		$response = $route->run($factory->createServerRequest("GET", $pattern));
		$this->assertNotEquals($staticFilepath, $response->getBody()->getMetadata("uri"));
	}


	public function testUseCompiledFile(): void
	{
		$route = $this->app->getSlim()->get("/compiled", BuildtimeTestController::class)
			->addMiddleware(new CompiledRouteMiddleware());
		$pattern = $route->getPattern();

		$this->execute(CompileRoutesCommand::class);

		$staticFilepath = Path::storage("framework/routes/static/" . hash('sha256', $pattern));

		$factory = new ServerRequestFactory();
		$response = $route->run($factory->createServerRequest("GET", $pattern));
		$this->assertEquals($staticFilepath, $response->getBody()->getMetadata("uri"));
	}
}
