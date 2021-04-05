<?php

namespace Eliepse\Argile\Filesystem;

use Eliepse\Argile\Core\Application;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class StorageRepository
{
	/** @var array<string, Filesystem> */
	private array $drivers = [];


	public function __construct(private array $driversConfig)
	{
		$this->makeDriverWithConfig("default", ["path" => Application::getInstance()->getProjectDirectory()]);
	}


	private function makeDriverWithConfig(string $name, array $config): Filesystem
	{
		if (empty($path = $config["path"])) {
			throw new \InvalidArgumentException("Invalid path for filesystem driver '$name': '$path'");
		}

		// TODO(eliepse): handle other adapters
		$adapter = new LocalFilesystemAdapter($path);
		return $this->drivers[$name] = new Filesystem($adapter);
	}


	private function makeDriver(string $name): Filesystem
	{
		if (empty($this->driversConfig[$name])) {
			throw new \InvalidArgumentException("No configuration found for filesystem driver '$name'.");
		}
		return $this->makeDriverWithConfig($name, $this->driversConfig[$name]);
	}


	public function getDriver(string $name = null): ?Filesystem
	{
		if (null === $name) {
			return $this->drivers["default"];
		}

		if (! empty($this->drivers[$name])) {
			return $this->drivers[$name];
		}

		return $this->makeDriver($name);
	}
}