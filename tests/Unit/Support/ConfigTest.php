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


	public function testReturnsDefaultOnUndefinedNamespace(): void
	{
		$this->assertNull(Config::get("unknown.foo"));
		$this->assertEquals("default!", Config::get("unknown.bar", "default!"));
	}
}