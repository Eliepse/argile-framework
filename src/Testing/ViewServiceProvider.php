<?php

namespace Eliepse\Argile\Testing;

final class ViewServiceProvider extends \Eliepse\Argile\Providers\ViewServiceProvider
{
	protected function getViewDirectory(): string
	{
		return __DIR__ . "/../../tests/View/fixtures/%name%";
	}
}