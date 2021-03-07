<?php

namespace Eliepse\Argile\Tests\Unit\Config;

use Eliepse\Argile\Config\Configuration;

final class ConfigurationTest extends \Eliepse\Argile\Tests\TestCase
{
	public function testGetValues(): void
	{
		$config = $this->getConfiguration();

		$this->assertEquals("test", $config->getNamespace());
		$this->assertEquals("bar", $config->get("foo"));
		$this->assertEquals("cache/", $config->get("compile.path"));
		$this->assertEquals(["footer", "header", "homepage"], $config->get("compile.views"));
	}


	public function testNonSetValueReturnDefault(): void
	{
		$config = $this->getConfiguration();

		$this->assertEquals("hey", $config->get("baboo", "hey"));
		$this->assertEquals("hoy", $config->get("compile.notAKey", "hoy"));
		$this->assertEquals("hou", $config->get("compile.not.a.key", "hou"));
	}


	private function getConfiguration(): Configuration
	{
		return new Configuration(
			"test",
			[
				"foo" => "bar",
				"compile" => [
					"path" => "cache/",
					"views" => [
						"footer",
						"header",
						"homepage",
					],
				],
			],
		);
	}
}