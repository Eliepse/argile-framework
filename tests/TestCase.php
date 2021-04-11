<?php

namespace Eliepse\Argile\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Filesystem\StorageRepository;
use Eliepse\Argile\Support\Path;
use Symfony\Component\Console\Tester\CommandTester;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected Application $app;


	/** @noinspection PhpInternalEntityUsedInspection */
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->app = Application::init(__DIR__);
		$this->app->withTestEnvironment()
			->withConfigPath(Path::root("Fixtures/configs/"));

		$this->app->register(Cache::class, function () {
			return new ArrayCache();
		});

		$this->app->boot();
	}


	protected function execute(string $command, array $inputs = []): CommandTester
	{
		$tester = new CommandTester($this->app->container->make($command));
		$tester->execute($inputs);
		return $tester;
	}


	protected function tearDown(): void
	{
		/** @var StorageRepository $storage */
		$storage = $this->app->resolve(StorageRepository::class);
		$storage->getDriver()->deleteDirectory("/cache");
		$storage->getDriver()->deleteDirectory("/bootstrap");
		$storage->getDriver("storage")->deleteDirectory("/");
	}
}