<?php

namespace Eliepse\Argile\Core;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\PhpFileCache;
use Eliepse\Argile\Providers\CacheProvider;
use Eliepse\Argile\Providers\ConfigurationProvider;
use Eliepse\Argile\Providers\EnvironmentProvider;
use Eliepse\Argile\Providers\LogProvider;
use Eliepse\Argile\Providers\ProviderInterface;
use Eliepse\Argile\Providers\ViewProvider;
use ErrorException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function DI\factory as DIFactory;

final class Application
{
	private static self $_instance;
	private string $project_directory;

	private \Slim\App $app;
	private PhpFileCache $cache;
	private Logger $logger;
	public Container $container;

	/**
	 * @var string[]
	 */
	public static array $defaultProviders = [
		EnvironmentProvider::class,
		ConfigurationProvider::class,
		LogProvider::class,
		CacheProvider::class,
		ViewProvider::class,
	];


	private function __construct(string $project_directory)
	{
		if (! is_dir($project_directory)) {
			throw new ErrorException("The project directory is not a valid or does not exist ($project_directory).");
		}
		$this->project_directory = $project_directory;
	}


	public static function init(string $projectRoot): self
	{
		return self::$_instance = new self($projectRoot);
	}


	/**
	 * @throws \Exception
	 * @deprecated Use boot() instead
	 */
	public function loadSlim(): void
	{
		$this->boot();
	}


	/**
	 * @param string[] $providersClassnames
	 *
	 * @throws \Exception
	 */
	public function boot(array $providersClassnames = []): void
	{
		$builder = new ContainerBuilder();
		$builder->useAutowiring(true);
		$builder->useAnnotations(false);
		$this->container = $builder->build();

		$this->container->set(Application::class, $this);

		// TODO: load providers from configs
		/** @var ProviderInterface[] $providers */
		$providers = array_map(
			fn($classname) => new $classname($this),
			array_filter($providersClassnames, fn($classname) => is_a($classname, ProviderInterface::class, true))
		);

		foreach ($providers as $provider) {
			$provider->register();
		}

		foreach ($providers as $provider) {
			$provider->boot();
		}

		$this->app = Bridge::create($this->container);
	}


	/**
	 * Register an element to be used in dependancy injection.
	 *
	 * @param string $name Generally an interface name
	 * @param callable $register An anonymous function that return the object
	 */
	public function register(string $name, callable $register): void
	{
		$this->container->set($name, DIFactory($register));
	}


	/**
	 * Resolve an object that has been stored in the container.
	 *
	 * @param string $name The classname, or some other key
	 *
	 * @return mixed
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function resolve(string $name): mixed
	{
		return $this->container->get($name);
	}


	/**
	 * @return Application
	 */
	public static function getInstance(): self
	{
		if (empty(self::$_instance)) {
			/** @noinspection PhpUnhandledExceptionInspection */
			throw new ErrorException(self::class . "has not been initialized.");
		}
		return self::$_instance;
	}


	public function getProjectDirectory(): string
	{
		return $this->project_directory;
	}


	public function getSlim(): \Slim\App
	{
		return $this->app;
	}


	public function getCache(): PhpFileCache
	{
		return $this->cache;
	}


	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}


	public function run(): void
	{
		$this->app->run();
	}
}