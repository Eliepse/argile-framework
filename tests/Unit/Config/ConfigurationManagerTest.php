<?php

namespace Eliepse\Argile\Tests\Unit\Config;

use Eliepse\Argile\Config\ConfigRepository;

final class ConfigurationManagerTest extends \Eliepse\Argile\Tests\TestCase
{
	public function testSetConfiguration(): void
	{
		$repository = new ConfigRepository("");
		$repository->set("test", $configs = ["foo" => "bar"]);

		$this->assertEquals($configs, $repository->get("test"));
		$this->assertEquals("bar", $repository->get("test.foo"));
	}

	public function testNonSetValueReturnDefault(): void
	{
		$repository = new ConfigRepository("");
		$repository->set("test", $configs = ["foo" => "bar"]);

		$this->assertEquals("baz", $repository->get("test.unknown", "baz"));
		$this->assertEquals("baz", $repository->get("unknown", "baz"));
	}


	public function testLoadConfigurationFile(): void
	{
		$repository = new ConfigRepository(__DIR__ . "/../../Fixtures/configs/");
		$this->assertIsArray($repository->get("test"));
		$this->assertEquals("bar", $repository->get("test.foo"));
	}


	public function testCheckConfiguration(): void
	{
		$repository = new ConfigRepository(__DIR__ . "/../../Fixtures/configs/");
		$this->assertTrue($repository->has("test"));
		$this->assertFalse($repository->has("unknown"));
	}
}