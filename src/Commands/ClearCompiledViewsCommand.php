<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Filesystem\StorageRepository;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Flysystem\Filesystem;

final class ClearCompiledViewsCommand extends Command
{
	private ?Filesystem $fs;


	public function __construct(StorageRepository $repository)
	{
		parent::__construct();

		$this->fs = $repository->getDriver("views");
	}


	protected function configure()
	{
		$this->setName("clear:configs")
			->setDescription("Clear the compiled the configuration files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (! $this->fs) {
			$output->writeln("The filesystem's \"storage\" driver is not available.");
			return Command::FAILURE;
		}

		$this->fs->deleteDirectory(ViewStaticLoader::$pathSuffix);
		$output->writeln("Done clearing compiled views.");
		return Command::SUCCESS;
	}
}