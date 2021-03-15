<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileViewsCommand;
use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Config\Configuration;
use Eliepse\Argile\Tests\TestCase;
use Eliepse\Argile\View\Loaders\GraveurTemplateReference;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\ViewFactory;
use Symfony\Component\Console\Command\Command;

class CompileViewsCommandTest extends TestCase
{
	public function testCompilationDisabled(): void
	{
		$tester = $this->execute(CompileViewsCommand::class);
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertEquals("View compilation disabled.\n", $tester->getDisplay(true));
	}


	public function testNothingToCompile(): void
	{
		/**
		 * @var ConfigRepository $configs
		 * @var ViewFactory $viewFactory
		 */
		$configs = $this->app->resolve(ConfigRepository::class);
		$configs->get("view"); // Load the config file
		$configs->set("view.compile.enable", true);

		$tester = $this->execute(CompileViewsCommand::class);
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertEquals("No view to compile.\n", $tester->getDisplay(true));
	}


	public function testCompiledViews(): void
	{
		/**
		 * @var ConfigRepository $configs
		 * @var ViewFactory $viewFactory
		 */
		$configs = $this->app->resolve(ConfigRepository::class);
		$viewFactory = $this->app->resolve(ViewFactory::class);

		/** @var ViewStaticLoader $staticLoader */
		$staticLoader = $viewFactory->getLoaders()["static"];
		$filename = $staticLoader->getHashedFilename(new GraveurTemplateReference("hello"));

		$configs->set("view.compile.enable", true);
		$configs->set("view.compile.views", ["hello"]);

		$tester = $this->execute(CompileViewsCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists(__DIR__ . "/../../cache/framework/views/static/$filename");
		$this->assertEquals("Hello World ", file_get_contents(__DIR__ . "/../../cache/framework/views/static/$filename"));
	}
}
