<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\Support\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

final class CompiledRouteMiddleware implements \Psr\Http\Server\MiddlewareInterface
{

	/**
	 * @inheritDoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($request->getMethod() !== "GET") {
			return $handler->handle($request);
		}

		$filepath = Path::storage("framework/routes/static/" . hash('sha256', $request->getRequestTarget()));

		if (file_exists($filepath)) {
			return new Response(body: (new StreamFactory())->createStreamFromFile($filepath));
		}

		return $handler->handle($request);
	}
}