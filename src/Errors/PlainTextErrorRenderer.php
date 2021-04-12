<?php

namespace Eliepse\Argile\Errors;

use Throwable;

final class PlainTextErrorRenderer extends \Slim\Error\Renderers\PlainTextErrorRenderer
{
	public function __invoke(Throwable $exception, bool $displayErrorDetails): string
	{
		ob_clean();
		return parent::__invoke($exception, $displayErrorDetails);
	}
}