<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Flash\Messages;

class FlashFormInputsMiddleware implements MiddlewareInterface
{
	private array $allowedContentType = [
		"application/x-www-form-urlencoded",
		"multipart/form-data",
	];


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (strtoupper($request->getMethod()) === "GET") {
			return $handler->handle($request);
		}

		if (!in_array(strtolower($request->getHeaderLine("content-type")), $this->allowedContentType, true)) {
			return $handler->handle($request);
		}

		$inputs = $request->getParsedBody();
		foreach ($inputs as $name => $value) {
			App::getInstance()->container->get(Messages::class)->addMessage("old.$name", $value);
		}

		return $handler->handle($request);
	}
}