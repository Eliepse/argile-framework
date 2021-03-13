<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileRoutesCommand;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\ConsoleTestCase;
use Eliepse\Argile\Tests\fixtures\Controllers\BuildtimeTestController;
use Symfony\Component\Console\Command\Command;

class CompileRoutesCommandTest extends ConsoleTestCase
{
	public function testNothingToCompile(): void
	{
		$tester = $this->execute(CompileRoutesCommand::class);
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertEquals("No route to compile.\n", $tester->getDisplay(true));
	}


	public function testCompiledRoutes(): void
	{
		$route = $this->app->getSlim()->get("/toCompile", BuildtimeTestController::class);
		$filename = hash('sha256', $route->getPattern());

		$tester = $this->execute(CompileRoutesCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists(Path::storage("framework/routes/static/$filename"));
		$this->assertEquals("Hello World ", file_get_contents(Path::storage("framework/routes/static/$filename")));
	}
}
