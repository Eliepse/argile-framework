<?php

namespace Eliepse\Argile\Tests\Fixtures\Controllers;

use Eliepse\Argile\Http\Controllers\BuildtimeController;
use Eliepse\Argile\Http\Responses\ViewResponse;
use Psr\Http\Message\ResponseInterface;

final class BuildtimeTestController implements BuildtimeController
{
	public function __invoke(): ResponseInterface
	{
		return new ViewResponse("hello");
	}
}