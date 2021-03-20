<?php

namespace Eliepse\Argile\Tests\Unit\Commands;

use Eliepse\Argile\Commands\CompileConfigsCommand;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\Tests\TestCase;
use Symfony\Component\Console\Command\Command;

class CompileConfigsCommandTest extends TestCase
{
	public function testCompileConfigs(): void
	{
		$cachePath = Path::root("bootstrap/cache/configs.php");
		$tester = $this->execute(CompileConfigsCommand::class);

		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
		$this->assertFileExists($cachePath);

		/** @noinspection PhpIncludeInspection */
		$envs = include $cachePath;
		$this->assertIsArray($envs);
	}
}
