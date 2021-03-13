<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileViewsCommand;
use Eliepse\Argile\Config\Configuration;
use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\Tests\ConsoleTestCase;
use Eliepse\Argile\View\Loaders\GraveurTemplateReference;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\ViewFactory;
use Symfony\Component\Console\Command\Command;

class CompileViewsCommandTest extends ConsoleTestCase
{
	public function testNothingToCompile(): void
	{
		$tester = $this->execute(CompileViewsCommand::class);
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertEquals("No view to cache.\n", $tester->getDisplay(true));
	}


	public function testCompiledViews(): void
	{
		/**
		 * @var ConfigurationManager $configs
		 * @var ViewFactory $viewFactory
		 */
		$configs = $this->app->resolve(ConfigurationManager::class);
		$viewFactory = $this->app->resolve(ViewFactory::class);

		/** @var ViewStaticLoader $staticLoader */
		$staticLoader = $viewFactory->getLoaders()["static"];
		$filename = $staticLoader->getHashedFilename(new GraveurTemplateReference("hello"));

		$configs->set(new Configuration("view", ["compile" => ["hello"]]));
		$tester = $this->execute(CompileViewsCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists(__DIR__ . "/../../cache/framework/views/static/$filename");
		$this->assertEquals("Hello World ", file_get_contents(__DIR__ . "/../../cache/framework/views/static/$filename"));
	}
}
