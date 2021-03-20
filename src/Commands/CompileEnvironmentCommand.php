<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Support\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CompileEnvironmentCommand extends Command
{
	static protected $defaultName = "compile:env";


	public function __construct(
		private Filesystem $fs,
		string $name = null
	)
	{
		parent::__construct($name);
	}


	protected function configure()
	{
		$this->setDescription("Compile the environment file.")
			->setHelp("To prevent parsing the .env at each runtime, this compile it as a php array stored in a file.");
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->fs->dumpFile(Path::root("bootstrap/cache/env.php"), "<?php return " . var_export($_ENV, true) . ";");
		$output->writeln("Done compiling environment.");
		return Command::SUCCESS;
	}
}