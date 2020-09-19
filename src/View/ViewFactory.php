<?php

namespace Eliepse\Argile\View;

use Eliepse\Argile\App;

final class ViewFactory
{
	/**
	 * @param string $name
	 * @param array<string, mixed> $values
	 *
	 * @return string
	 * @throws \ErrorException
	 */
	public static function make(string $name, array $values = []): string
	{
		$engine = App::getInstance()->getTemplateEngine();
		$name .= pathinfo($name, PATHINFO_EXTENSION) ?: ".view";

		foreach ($values as $key => $value) {
			/** @noinspection PhpPossiblePolymorphicInvocationInspection */
			/** @phpstan-ignore-next-line */
			$engine->addGlobal($key, $value);
		}
		return $engine->render($name);
	}
}