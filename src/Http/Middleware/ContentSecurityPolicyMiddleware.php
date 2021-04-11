<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\Config\ConfigRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{

	private bool $reportOnly;
	private string $defaultSrc;
	/** @var array<string, string> */
	private array $directives;


	/**
	 * ContentSecurityPolicyMiddleware constructor.
	 */
	public function __construct(ConfigRepository $configs)
	{
		$this->reportOnly = $configs->get("security.csp.reportOnly", false);
		$this->defaultSrc = $configs->get("security.csp.defaultSrc", "'self'");
		$this->directives = $configs->get("security.csp.directives", []);
	}


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$headerValue = ["default-src $this->defaultSrc"];

		foreach ($this->directives as $name => $directive) {
			$headerValue[] = "$name " . ($directive ?? $this->defaultSrc);
		}

		$response = $handler->handle($request);

		return $response->withHeader(
			$this->reportOnly ? "Content-Security-Policy-Report-Only" : "Content-Security-Policy",
			join("; ", $headerValue)
		);
	}
}