<?php

namespace Eliepse\Argile\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{

	private bool $reportOnly;
	private string $defaultSrc;
	private array $directives;


	public function __construct(
		bool $reportOnly = false,
		string $defaultSrc = "'self'",
		array $directives = []
	)
	{
		$this->reportOnly = $reportOnly;
		$this->defaultSrc = $defaultSrc;
		$this->directives = $directives;
	}


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{

		$headerValue = ["default-src {$this->defaultSrc}"];
		foreach ($this->directives as $name => $directive) {
//			$hash = base64_encode(random_bytes(16));
//			flash()->addMessage("hash.csp.$name", $hash);
			$headerValue[] = "$name " . ($directive ?? $this->defaultSrc);
		}

		$response = $handler->handle($request);

		return $response->withHeader(
			$this->reportOnly ? "Content-Security-Policy-Report-Only" : "Content-Security-Policy",
			join("; ", $headerValue)
		);
	}
}