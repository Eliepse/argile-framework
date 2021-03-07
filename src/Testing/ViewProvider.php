<?php

namespace Eliepse\Argile\Testing;

final class ViewProvider extends \Eliepse\Argile\Providers\ViewProvider
{
	protected function getStaticDirectory(): string
	{
		return "";
	}


	protected function getCacheDirectory(): string
	{
		return "";
	}


	protected function getViewDirectory(): string
	{
		return __DIR__ . "/../../tests/fixtures/views/";
	}
}