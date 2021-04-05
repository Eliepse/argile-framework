<?php

namespace Eliepse\Argile\Tests\Fixtures\Controllers;

use Eliepse\Argile\Http\Responses\ViewResponse;
use Psr\Http\Message\ResponseInterface;

final class BuildtimeTestController implements \Eliepse\Argile\Http\Controllers\BuildtimeController
{
	public function __invoke(): ResponseInterface
	{
		return new ViewResponse("hello");
	}
}