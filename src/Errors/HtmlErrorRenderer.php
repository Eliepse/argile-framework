<?php

namespace Eliepse\Argile\Errors;

use Throwable;

final class HtmlErrorRenderer extends \Slim\Error\Renderers\HtmlErrorRenderer
{
	public function __invoke(Throwable $exception, bool $displayErrorDetails): string
	{
		ob_clean();
		return parent::__invoke($exception, $displayErrorDetails);
	}
}