<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileEnvironmentCommand;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\TestCase;
use Symfony\Component\Console\Command\Command;

class CompileEnvironmentCommandTest extends TestCase
{
	public function testCompileEnvironment(): void
	{
		$cachePath = Path::root("bootstrap/cache/env.php");
		$tester = $this->execute(CompileEnvironmentCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists($cachePath);
		/** @noinspection PhpIncludeInspection */
		$envs = include $cachePath;
		$this->assertEquals($_ENV, $envs);
	}
}
