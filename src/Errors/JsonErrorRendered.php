<?php

namespace Eliepse\Argile\Errors;

use Throwable;

final class JsonErrorRendered extends \Slim\Error\Renderers\JsonErrorRenderer
{
	public function __invoke(Throwable $exception, bool $displayErrorDetails): string
	{
		ob_clean();
		return parent::__invoke($exception, $displayErrorDetails);
	}
}