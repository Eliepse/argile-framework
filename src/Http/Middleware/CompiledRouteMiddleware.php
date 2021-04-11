<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\Core\Environment;
use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Support\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class CompiledRouteMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
	private bool $enabled;


	public function __construct(Environment $envs)
	{
		$this->enabled = $envs->get("ROUTES_COMPILE", false);
	}


	/**
	 * @inheritDoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (! $this->enabled) {
			return $handler->handle($request);
		}

		/** @var RouteInterface|null $route */
		$route = $request->getAttribute(RouteContext::ROUTE);

		if (! is_null($route) && Router::isBuildtimeRoute($route)) {
			$filepath = Path::storage("framework/routes/static/" . $route->getIdentifier());
			return new Response(body: (new StreamFactory())->createStreamFromFile($filepath));
		}

		return $handler->handle($request);
	}
}