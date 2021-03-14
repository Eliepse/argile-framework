<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\ViewFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\Storage\StringStorage;

final class CompileViewsCommand extends Command
{
	static protected $defaultName = "compile:view";

	protected array $compilable = [];
	protected ViewStaticLoader $staticLoader;


	public function __construct(
		private ViewFactory $viewFactory,
		private ConfigurationManager $configs,
		string $name = null
	)
	{
		parent::__construct($name);

		$this->staticLoader = $this->viewFactory->getLoaders()["static"];

		if ($configs->has("view")) {
			$this->compilable = $configs->get("view")->get("compile.views", []);
		}
	}


	protected function configure()
	{
		$this->setDescription("Compile views as static files.")
			->setHelp("It compiles the views as listed in the configuration and cache them as static files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (true !== $this->configs->get("view")->get("compile.enable", false)) {
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
			$this->staticLoader->saveTemplate($reference, new StringStorage($content));
			$output->writeln(" - $viewName");
		}

		$output->writeln("Done compiling views.");
		return Command::SUCCESS;
	}
}