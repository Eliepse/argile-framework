<?php

namespace Eliepse\Argile\Tests\Unit\View;

use Eliepse\Argile\Tests\TestCase;
use Eliepse\Argile\View\ViewFactory;

class ViewFactoryTest extends TestCase
{
	public function testRenderSimpleTemplate(): void
	{
		$this->assertEquals("Hello World ", ViewFactory::make("hello"));
	}


	public function testRenderWithParameters(): void
	{
		$this->assertEquals(
			"My car is red and has 4 wheels",
			ViewFactory::make("car", ["color" => "red", "count" => 4])
		);
	}


	public function testRenderWithStructures(): void
	{
		$this->assertEquals(
			"My name is Elie.\nI like visiting my friends.",
			trim(ViewFactory::make("friends", ["name" => "Elie"]))
		);
		$this->assertEquals(
			"My name is Elie.\nMy friends are:\nMatthieu;\nFred;\nJulie;",
			trim(ViewFactory::make("friends", ["name" => "Elie", "friends" => ["Matthieu", "Fred", "Julie"]]))
		);
	}


	public function testRenderWithInclusions(): void
	{
		$this->assertEquals(
			"This village has:\n- A church;\n- 3 houses;",
			trim(ViewFactory::make("village", ["buildings" => [1 => "church", 3 => "house"]]))
		);
	}


	public function testCommentedMustachedInstructions(): void
	{
		$this->assertEquals(
			"Nothing here.",
			trim(ViewFactory::make("commented", ["foo" => "bar"]))
		);
	}
}
