<?php

namespace Eliepse\Argile\Http\Middleware;

use DateInterval;
use DateTime;
use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Http\Responses\ViewResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class MaintenanceMiddleware implements MiddlewareInterface
{
	private ?string $viewPath;
	private bool $isMaintenance;
	private string $token;
	private string $tokenKey = "maintenanceToken";


	public function __construct(bool $isMaintenance = false, string $viewPath = null)
	{
		$this->viewPath = $viewPath;
		$this->isMaintenance = $isMaintenance;

		if ($this->isMaintenance) {
			$this->token = $this->getOrNewToken();
		}
	}


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (! $this->isMaintenance) {
			return $handler->handle($request);
		}

		$queryToken = $request->getQueryParams()["bypassToken"] ?? null;

		if ($this->validateSessionBypass()) {
			$this->resetSessionBypass();
			return $handler->handle($request);
		}

		if (! empty($queryToken) && $queryToken === $this->token) {
			$this->resetSessionBypass();
			return $handler->handle($request);
		}

		return $this->viewPath ? (new ViewResponse($this->viewPath))->withStatus(503) : new Response(503);
	}


	private function generateBypassToken(): string
	{
		$token = base64_encode(random_bytes(32));
		Application::getInstance()->getCache()->save($this->tokenKey, $token, 0);
		return $token;
	}


	private function getOrNewToken(): string
	{
		if (Application::getInstance()->getCache()->contains($this->tokenKey)) {
			return Application::getInstance()->getCache()->fetch($this->tokenKey);
		}

		return $this->generateBypassToken();
	}


	private function resetSessionBypass(): void
	{
		$expiresIn = new DateInterval("P15M");
		$_SESSION[ $this->tokenKey ] = [
			"expires_at" => (new DateTime())->add($expiresIn)->getTimestamp(),
			"token" => $this->token,
		];
	}


	private function validateSessionBypass(): bool
	{
		if (! isset($_SESSION[ $this->tokenKey ])) {
			return false;
		}

		$expires_at = $_SESSION[ $this->tokenKey ]["expires_at"] ?? 0;
		$token = $_SESSION[ $this->tokenKey ]["token"] ?? null;

		if ($expires_at < (new DateTime())->getTimestamp()) {
			return false;
		}

		return ! empty($token) && $token === $this->token;
	}
}