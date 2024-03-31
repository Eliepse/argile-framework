<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileRoutesCommand;
use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\Fixtures\Controllers\BuildtimeTestController;
use Eliepse\Argile\Tests\Fixtures\Controllers\RuntimeTestController;
use Eliepse\Argile\Tests\TestCase;
use Symfony\Component\Console\Command\Command;

class CompileRoutesCommandTest extends TestCase
{
	public function testNothingToCompile(): void
	{
		$route = Router::get("/runtime", RuntimeTestController::class);
		$tester = $this->execute(CompileRoutesCommand::class);
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileDoesNotExist(Path::storage("framework/routes/static/" . $route->getIdentifier()));
		$this->assertEquals("No route to compile.\n", $tester->getDisplay(true));
	}


	public function testCompiledRoutes(): void
	{
		Router::get("/runtime", RuntimeTestController::class);
		Router::get("/buildtime", BuildtimeTestController::class);
		$filename = crc32("/buildtime");

		$tester = $this->execute(CompileRoutesCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists(Path::storage("framework/routes/static/$filename"));
		$this->assertEquals("Hello World ", file_get_contents(Path::storage("framework/routes/static/$filename")));
	}
}
