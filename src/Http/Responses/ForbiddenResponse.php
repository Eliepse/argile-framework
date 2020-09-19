<?php

namespace Eliepse\Argile\Http\Responses;


use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Response;

class ForbiddenResponse extends Response
{
	public function __construct()
	{
		parent::__construct(StatusCodeInterface::STATUS_FORBIDDEN);
	}
}