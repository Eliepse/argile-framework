<?php

namespace Eliepse\Argile\Http\Middleware;

use Eliepse\Argile\Config\ConfigRepository;
use Middlewares\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SessionMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
	private PhpSession $phpSession;


	public function __construct(ConfigRepository $configs)
	{
		$this->phpSession = new PhpSession();

		$this->phpSession->name($configs->get("app.session.name", "argile_session"));

		$this->phpSession->options([
			'use_strict_mode' => true,
			'cookie_httponly' => true,
			'use_only_cookies' => true,
			'sid_length' => 64,
			'sid_bits_per_character' => 6,
			'cookie_lifetime' => $configs->get("app.session.lifetime", 3_600 * 24 * 14),
		]);
	}


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		return $this->phpSession->process($request, $handler);
	}
}