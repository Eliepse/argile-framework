<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Filesystem\StorageRepository;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\ViewFactory;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CompileViewsCommand extends Command
{
	protected array $compilable = [];
	protected ViewStaticLoader $staticLoader;
	private ?Filesystem $fs;


	public function __construct(
		private ViewFactory $viewFactory,
		private ConfigRepository $configs,
		StorageRepository $storageRepository,
	)
	{
		parent::__construct();

		$this->staticLoader = $this->viewFactory->getLoaders()["static"];
		$this->fs = $storageRepository->getDriver("views");
		$this->compilable = $configs->get("view.compile.views", []);
	}


	protected function configure()
	{
		$this
			->setName("compile:view")
			->setDescription("Compile views as static files.")
			->setHelp("It compiles the views as listed in the configuration and cache them as static files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (! $this->fs) {
			$output->writeln("The filesystem's \"storage\" driver is not available.");
			return Command::FAILURE;
		}

		if (true !== $this->configs->get("view.compile.enable", false)) {
			$output->writeln("View compilation disabled.");
			return Command::SUCCESS;
		}

		if (empty($this->compilable)) {
			$output->writeln("No view to compile.");
			return Command::SUCCESS;
		}

		$output->writeln("Start compiling views:");

		foreach ($this->compilable as $viewName) {
			$reference = $this->viewFactory->getViewReference($viewName);
			$content = $this->viewFactory->render($viewName);
			$this->fs->write(ViewStaticLoader::$pathSuffix . $this->staticLoader->getHashedFilename($reference), $content);
			$output->writeln(" - $viewName");
		}

		$output->writeln("Done compiling views.");
		return Command::SUCCESS;
	}
}