<?php

namespace Eliepse\Argile\Tests\Fixtures\Controllers;

use Eliepse\Argile\Http\Responses\ViewResponse;
use Psr\Http\Message\ResponseInterface;

final class RuntimeTestController
{
	public function __invoke(): ResponseInterface
	{
		return new ViewResponse("hello");
	}
}