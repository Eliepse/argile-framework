<?php

namespace Eliepse\Argile\Tests\View;

use Eliepse\Argile\Tests\TestCase;
use Eliepse\Argile\View\ViewFileSystemLoader;
use Symfony\Component\Templating\TemplateNameParser;

class ViewFileSystemLoaderTest extends TestCase
{
	private TemplateNameParser $nameParser;


	protected function setUp(): void
	{
		$this->nameParser = new TemplateNameParser();
	}


	public function testLoadAbsolutePath(): void
	{
		$loader = $this->getLoader();
		$storage = $loader->load($this->nameParser->parse(__DIR__ . "/fixtures/hello.view"));
		$this->assertNotFalse($storage);
		$this->assertEquals("Hello World <?php # comment ?>", $storage->getContent());
	}


	public function testLoadBasicView(): void
	{
		$loader = $this->getLoader();
		$storage = $loader->load($this->nameParser->parse("hello.view"));
		$this->assertNotFalse($storage);
		$this->assertEquals("Hello World <?php # comment ?>", $storage->getContent());
	}


	public function testViewNotFound(): void
	{
		$loader = $this->getLoader();
		$storage = $loader->load($this->nameParser->parse("unknown.view"));
		$this->assertFalse($storage);
	}


	private function getLoader(): ViewFileSystemLoader
	{
		$filesystem = new ViewFileSystemLoader([__DIR__ . "/fixtures"]);
		$filesystem->setLogger($this->logger);
		return new ViewFileSystemLoader([__DIR__ . "/fixtures/%name%"]);
	}
}
