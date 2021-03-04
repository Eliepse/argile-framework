<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\Core\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Flash\Messages;

class FlashFormInputsMiddleware implements MiddlewareInterface
{
	/**
	 * @var array|string[]
	 */
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

		$inputs = $request->getParsedBody() ?? [];

		// TODO(eliepse): handle the case when an object is return by `getParsedBody()`
		if(is_object($inputs)) {
			return $handler->handle($request);
		}

		foreach ($inputs as $name => $value) {
			Application::getInstance()->container->get(Messages::class)->addMessage("old.$name", $value);
		}

		return $handler->handle($request);
	}
}