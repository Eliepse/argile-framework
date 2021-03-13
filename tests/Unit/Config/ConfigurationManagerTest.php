<?php

namespace Eliepse\Argile\Tests\Unit\Config;

use Eliepse\Argile\Config\Configuration;
use Eliepse\Argile\Config\ConfigurationManager;

final class ConfigurationManagerTest extends \Eliepse\Argile\Tests\TestCase
{
	public function testSetConfiguration(): void
	{
		$manager = new ConfigurationManager("");
		$manager->set($config = new Configuration("test", ["foo" => "bar"]));

		$this->assertEquals($config, $manager->get("test"));
		$this->assertEquals("bar", $manager->get("test")->get("foo"));
	}


	public function testLoadConfigurationFile(): void
	{
		$manager = new ConfigurationManager(__DIR__ . "/../../Fixtures/config/");
		$this->assertIsObject($manager->get("test"));
		$this->assertEquals("bar", $manager->get("test")->get("foo"));
	}


	public function testCheckConfiguration(): void
	{
		$manager = new ConfigurationManager(__DIR__ . "/../../Fixtures/config/");
		$this->assertTrue($manager->has("test"));
		$this->assertFalse($manager->has("unknown"));
	}
}