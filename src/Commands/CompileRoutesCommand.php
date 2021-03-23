<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Http\Controllers\BuildtimeController;
use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Support\Path;
use Slim\Interfaces\RouteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CompileRoutesCommand extends Command
{
	static protected $defaultName = "compile:routes";

	/** @var RouteInterface[] */
	protected array $compilable = [];


	public function __construct(
		private Application $app,
		private Filesystem $fs,
		string $name = null
	)
	{
		parent::__construct($name);
	}


	protected function configure()
	{
		$this->setDescription("Compile routes output as static files.")
			->setHelp("It renders the elligible routes and store them as static files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$allRoutes = $this->app->getSlim()->getRouteCollector()->getRoutes();
		$this->compilable = Router::getBuildtimRoutes();

		if (empty($this->compilable)) {
			$output->writeln("No route to compile.");
			return Command::SUCCESS;
		}

		$requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
		$output->writeln("Start compiling routes:");

		foreach ($this->compilable as $id => $route) {
			$request = $requestFactory->createServerRequest("GET", $route->getPattern());
			$this->fs->dumpFile(
				Path::storage("framework/routes/static/$id"),
				$route->run($request)->getBody()
			);

			$output->writeln(" - " . $route->getPattern());
		}

		$output->writeln("Done compiling routes.");
		return Command::SUCCESS;
	}
}