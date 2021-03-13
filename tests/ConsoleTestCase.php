<?php

namespace Eliepse\Argile\Tests;

use Symfony\Component\Console\Tester\CommandTester;

class ConsoleTestCase extends TestCase
{
	protected function execute(string $command, array $inputs = []): CommandTester
	{
		$tester = new CommandTester($this->app->container->make($command));
		$tester->execute($inputs);
		return $tester;
	}
}