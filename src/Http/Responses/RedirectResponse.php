<?php

namespace Eliepse\Argile\Http\Responses;


use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;

class RedirectResponse extends Response
{
	public function __construct(
		string $location,
		?HeadersInterface $headers = null
	)
	{
		$headers = $headers ?? new Headers();
		$headers->setHeader("Location", $location);

		parent::__construct(
			StatusCodeInterface::STATUS_FOUND,
			$headers,
			null
		);
	}
}