<?php

namespace Eliepse\Argile\Errors;

use Throwable;

final class XmlErrorRenderer extends \Slim\Error\Renderers\XmlErrorRenderer
{
	public function __invoke(Throwable $exception, bool $displayErrorDetails): string
	{
		ob_clean();
		return parent::__invoke($exception, $displayErrorDetails);
	}
}