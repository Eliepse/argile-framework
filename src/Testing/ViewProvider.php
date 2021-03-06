<?php

namespace Eliepse\Argile\Testing;

final class ViewProvider extends \Eliepse\Argile\Providers\ViewProvider
{
	protected function getViewDirectory(): string
	{
		return __DIR__ . "/../../tests/View/fixtures/%name%";
	}
}