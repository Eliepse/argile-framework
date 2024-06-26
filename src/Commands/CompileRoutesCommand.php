<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Filesystem\StorageRepository;
use Eliepse\Argile\Http\Controllers\BuildtimeController;
use Eliepse\Argile\Http\Router;
use Eliepse\Argile\Support\Path;
use Slim\Interfaces\RouteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Flysystem\Filesystem;

final class CompileRoutesCommand extends Command
{
	/** @var RouteInterface[] */
	protected array $compilable = [];
	/**
	 * @var Filesystem|null
	 */
	private ?Filesystem $fs;


	public function __construct(private Application $app, StorageRepository $fsRepository)
	{
		parent::__construct();

		$this->fs = $fsRepository->getDriver("storage");
	}


	protected function configure()
	{
		$this->setName("compile:routes")
			->setDescription("Compile routes output as static files.")
			->setHelp("It renders the elligible routes and store them as static files.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (! $this->fs) {
			$output->writeln("The filesystem's \"storage\" driver is not available.");
			return Command::FAILURE;
		}

		$this->compilable = Router::getBuildtimRoutes();

		if (empty($this->compilable)) {
			$output->writeln("No route to compile.");
			return Command::SUCCESS;
		}

		$requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
		$output->writeln("Start compiling routes:");

		foreach ($this->compilable as $route) {
			$request = $requestFactory->createServerRequest("GET", $route->getPattern());
            $hash = crc32($request->getRequestTarget());

			$this->fs->write("framework/routes/static/$hash", $route->run($request)->getBody());

			$output->writeln(" - {$route->getPattern()} (hash: $hash)");
		}

		$output->writeln("Done compiling routes.");
		return Command::SUCCESS;
	}
}