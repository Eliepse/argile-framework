<?php

namespace Eliepse\Argile\Testing;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

final class ViewProvider extends \Eliepse\Argile\Providers\ViewProvider
{
	protected function getStaticDirectory(): string
	{
		return __DIR__ . "/../../tests/cache/framework/views/static/";
	}


	protected function getCache(): Cache
	{
		return new ArrayCache();
	}


	protected function getViewDirectory(): string
	{
		return __DIR__ . "/../../tests/fixtures/views/";
	}
}