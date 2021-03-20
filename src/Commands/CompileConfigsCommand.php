<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Support\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CompileConfigsCommand extends Command
{
	static protected $defaultName = "compile:configs";


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
		$this->setDescription("Compile the configuration files.")
			->setHelp("Prevent fetching multiple files by processing and combining configurations into a single file.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$configs = [];
		$fwConfigPath = __DIR__ . "/../../configs/";
		$appConfigPath = $this->app->getConfigPath();

		// Only takes php files
		$frameworkFiles = array_filter(scandir($fwConfigPath), fn($filename) => str_ends_with($filename, ".php"));
		$appFiles = array_filter(scandir($appConfigPath), fn($filename) => str_ends_with($filename, ".php"));

		// We filter the required configs from the framework
		// that has not been overwritten in the app.
		$frameworkFiles = array_diff($frameworkFiles, $appFiles);

		// We append the full path to config files and create
		// an array of files to load.
		$filesToLoad = array_merge(
			array_map(fn($fileName) => $fwConfigPath . $fileName, $frameworkFiles),
			array_map(fn($fileName) => $appConfigPath . $fileName, $appFiles)
		);

		foreach ($filesToLoad as $configPath) {
			$namespace = pathinfo($configPath, PATHINFO_FILENAME);
			$configs[$namespace] = include $configPath;
			$output->writeln("\n- $namespace");
		}

		$this->fs->dumpFile(Path::root("bootstrap/cache/configs.php"), "<?php return " . var_export($configs, true) . ";");

		$output->writeln("Done compiling configs.");
		return Command::SUCCESS;
	}
}