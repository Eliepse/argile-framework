<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Filesystem\StorageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Flysystem\Filesystem;

final class ClearCompiledRoutesCommand extends Command
{
	/**
	 * @var Filesystem|null
	 */
	private ?Filesystem $fs;


	public function __construct(private StorageRepository $repository)
	{
		parent::__construct();

		$this->fs = $this->repository->getDriver("storage");
	}


	protected function configure()
	{
		$this->setName("compile:routes")
			->setDescription("Clear the compiled routes files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (! $this->fs) {
			$output->writeln("The filesystem's \"storage\" driver is not available.");
			return Command::FAILURE;
		}

		$this->fs->deleteDirectory("framework/routes/static");
		$output->writeln("Done clearing environment.");
		return Command::SUCCESS;
	}
}