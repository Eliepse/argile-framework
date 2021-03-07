<?php

namespace Eliepse\Argile\Tests\Unit\Support;

use Eliepse\Argile\Support\Config;

final class ConfigTest extends \Eliepse\Argile\Tests\TestCase
{
	public function testGet(): void
	{
		$this->assertEquals("bar", Config::get("test.foo"));
		$this->assertEquals("baz", Config::get("test.notAKey", "baz"));
	}


	public function testThrowErrorOnUndefinedNamespace(): void
	{
		$namespace = "unknown";
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage("Unable to load a configuration file: $namespace");
		Config::get("$namespace.foo");
	}
}