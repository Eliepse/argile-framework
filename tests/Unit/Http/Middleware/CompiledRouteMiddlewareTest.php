<?php

namespace Eliepse\Argile\Tests\Unit\Http\Middleware;

use Eliepse\Argile\Commands\CompileRoutesCommand;
use Eliepse\Argile\Core\Environment;
use Eliepse\Argile\Core\EnvironmentInterface;
use Eliepse\Argile\Http\Middleware\CompiledRouteMiddleware;
use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\Fixtures\Controllers\BuildtimeTestController;
use Eliepse\Argile\Tests\Fixtures\Controllers\RuntimeTestController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Uri;

class CompiledRouteMiddlewareTest extends \Eliepse\Argile\Tests\TestCase
{
	private EnvironmentInterface|Environment $env;
	private ServerRequestFactory $factory;


	protected function setUp(): void
	{
		parent::setUp();
		$this->env = $this->app->resolve(EnvironmentInterface::class);
		$this->env->getRepository()->set("ROUTES_COMPILE", true);
		$this->factory = new ServerRequestFactory();
	}


	public function testRuntimeRoute(): void
	{
		$route = $this->app->getSlim()->get("/generated", RuntimeTestController::class)
			->addMiddleware($this->app->container->make(CompiledRouteMiddleware::class));

		$pattern = $route->getPattern();

		$this->execute(CompileRoutesCommand::class);

		$staticFilepath = Path::storage("framework/routes/static/" . hash('sha256', $pattern));

		$response = $route->run($this->factory->createServerRequest("GET", $pattern));
		$this->assertNotEquals($staticFilepath, $response->getBody()->getMetadata("uri"));
	}


	public function testBuildtimeRoute(): void
	{
		$route = Router::get("/compiled", BuildtimeTestController::class)
			->addMiddleware($this->app->container->make(CompiledRouteMiddleware::class));

		$this->execute(CompileRoutesCommand::class);
		$staticFilepath = Path::storage("framework/routes/static/" . $route->getIdentifier());

		$this->markTestIncomplete("Cannot make slim router work as expected.");

		$response = $route->run($this->factory->createServerRequest("GET", $route->getPattern()));
		$this->assertEquals($staticFilepath, $response->getBody()->getMetadata("uri"));
	}


	public function testShouldUseRuntime(): void
	{
		$this->env->getRepository()->set("ROUTES_COMPILE", false);

		$route = $this->app->getSlim()->get("/compiled", BuildtimeTestController::class)
			->addMiddleware($this->app->container->make(CompiledRouteMiddleware::class));
		$pattern = $route->getPattern();

		$this->execute(CompileRoutesCommand::class);

		$staticFilepath = Path::storage("framework/routes/static/" . hash('sha256', $pattern));

		$response = $route->run($this->factory->createServerRequest("GET", $pattern));
		$this->assertNotEquals($staticFilepath, $response->getBody()->getMetadata("uri"));
	}
}
