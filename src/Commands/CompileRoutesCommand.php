<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Http\Controllers\BuildtimeController;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\ViewFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CompileRoutesCommand extends Command
{
	static protected $defaultName = "compile:routes";

	protected array $compilable = [];
	protected ViewStaticLoader $staticLoader;


	public function __construct(
		private Application $app,
		private ViewFactory $viewFactory,
		private Filesystem $fs,
		string $name = null
	)
	{
		parent::__construct($name);

		$this->staticLoader = $this->viewFactory->getLoaders()["static"];
	}


	protected function configure()
	{
		$this->setDescription("Compile routes output as static files.")
			->setHelp("It renders the elligible routes and store them as static files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$routes = $this->app->getSlim()->getRouteCollector()->getRoutes();

		$this->compilable = array_filter($routes, function ($route) {
			$callableName = $route->getCallable();
			return is_string($callableName)
				&& in_array("GET", $route->getMethods())
				&& class_implements($callableName, BuildtimeController::class);
		});

		if (empty($this->compilable)) {
			$output->writeln("No route to compile.");
			return Command::SUCCESS;
		}

		$requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
		$output->writeln("Start compiling routes:");

		foreach ($this->compilable as $route) {
			$pattern = $route->getPattern();
			$request = $requestFactory->createServerRequest("GET", $pattern);


			$this->fs->dumpFile(
				Path::storage("framework/routes/static/" . hash('sha256', $pattern)),
				$route->run($request)->getBody()
			);

			$output->writeln(" - " . $pattern);
		}

		$output->writeln("Done compiling routes.");
		return Command::SUCCESS;
	}
}