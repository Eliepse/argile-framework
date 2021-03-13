<?php

namespace Eliepse\Argile\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Testing\EnvironmentProvider;
use Eliepse\Argile\Testing\LogProvider;
use Eliepse\Argile\Testing\ViewProvider;
use Symfony\Component\Filesystem\Filesystem;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected Application $app;


	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->app = Application::init(__DIR__);
		$this->app->boot([
			EnvironmentProvider::class,
			LogProvider::class,
			ViewProvider::class,
		]);

		$this->app->register(Cache::class, function () {
			return new ArrayCache();
		});

		$this->app->register(ConfigurationManager::class, function () {
			return new ConfigurationManager(__DIR__ . "/fixtures/config/");
		});
	}


	protected function tearDown(): void
	{
		/** @var Filesystem $fs */
		$fs = $this->app->resolve(Filesystem::class);
		$fs->remove(__DIR__ . "/cache");
	}
}