<?php

namespace Eliepse\Argile\Commands;

use Eliepse\Argile\Cache\CacheRepository;
use Eliepse\Argile\Config\ConfigRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ClearCacheCommand extends Command
{
	public function __construct(private CacheRepository $cacheRepository, private ConfigRepository $configRepository)
	{
		parent::__construct();
	}


	protected function configure()
	{
		$this->setName("cache:clear")
			->addArgument("store", InputArgument::OPTIONAL)
			->setDescription("Compile the configuration files.")
			->setHelp("Prevent fetching multiple files by processing and combining configurations into a single file.");
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$store = $input->getArgument("store");

		if (null !== $store) {
			return $this->clearStore($store, $output);
		}

		return $this->clearAllStores($output);
	}


	private function clearAllStores(OutputInterface $output): int
	{
		foreach ($this->configRepository->get("cache.stores", []) as $name => $config) {
			if (! $this->cacheRepository->getStore($name)->deleteAll()) {
				$output->writeln("Failed clearing cache store: $name");
				return Command::FAILURE;
			}
		}

		$output->writeln("All cache stores cleared.");
		return Command::SUCCESS;
	}


	private function clearStore(?string $name, OutputInterface $output): int
	{
		if (! $this->cacheRepository->getStore($name)->deleteAll()) {
			$output->writeln("Failed clearing cache store: $name");
			return Command::FAILURE;
		}

		$output->writeln("Cache store '$name' cleared.");
		return Command::SUCCESS;
	}
}