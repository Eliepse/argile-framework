<?php

namespace Eliepse\Argile\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Flysystem\Filesystem;

final class ClearCompiledEnvironmentCommand extends Command
{
	public function __construct(private Filesystem $fs)
	{
		parent::__construct();
	}


	protected function configure()
	{
		$this->setName("clear:env")
			->setDescription("Clear the compiled the environment files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->fs->delete("bootstrap/cache/env.php");
		$output->writeln("Done clearing environment.");
		return Command::SUCCESS;
	}
}