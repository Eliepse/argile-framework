<?php

namespace Eliepse\Argile\Core;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\PhpFileCache;
use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Providers\LogProvider;
use Eliepse\Argile\Providers\ProviderInterface;
use ErrorException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function DI\factory as DIFactory;

final class Application
{
	static private self $_instance;

	private string $project_directory;
	private ?string $environmentPath = null;
	private ?string $configPath = null;
	private \Slim\App $app;
	private PhpFileCache $cache;
	private Logger $logger;
	private bool $booted = false;
	private Environment $environment;

	public Container $container;


	private function __construct(string $project_directory)
	{
		if (! is_dir($project_directory)) {
			throw new ErrorException("The project directory is not a valid or does not exist ($project_directory).");
		}

		$this->project_directory = $project_directory;
		$this->environmentPath = $this->project_directory;

		if (getenv("APP_ENV") === "testing") {
			$this->environment = Environment::createMutableFromArray(array_merge(getenv()));
		} else {
			$this->environment = Environment::createFromFile($this->environmentPath);
		}

		$builder = new ContainerBuilder();
		$builder->useAutowiring(true);
		$builder->useAnnotations(false);

		if ($this->isProduction()) {
			$builder->enableCompilation($this->project_directory . "/bootstrap/cache/");
		}

		$this->container = $builder->build();
		$this->container->set(Application::class, $this);
	}


	public static function init(string $projectRoot): self
	{
		return self::$_instance = new self($projectRoot);
	}


	public function withBasePath($path): self
	{
		if ($this->booted) {
			return $this;
		}

		$this->project_directory = $path;
		return $this;
	}


	/**
	 * @param array $env
	 *
	 * @return $this
	 * @internal
	 */
	public function withTestEnvironment(array $env = []): self
	{
		if ($this->booted) {
			return $this;
		}

		$this->environment = Environment::createMutableFromArray(array_merge(getenv(), $env));

		return $this;
	}


	private function registerEnvironment(): void
	{
		if ($this->container->has(EnvironmentInterface::class)) {
			return;
		}

		$this->register(EnvironmentInterface::class, function () {
			return $this->environment;
		});
	}


	public function withConfigPath(string $path): self
	{
		if ($this->booted) {
			return $this;
		}

		$this->configPath = $path;

		return $this;
	}


	private function registerConfiguration(): void
	{
		$this->register(ConfigRepository::class, function () {
			return new ConfigRepository($this->configPath ?: $this->project_directory . DIRECTORY_SEPARATOR . "configs/");
		});
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
	 * @throws \Exception
	 */
	public function boot(): void
	{
		if ($this->booted) {
			return;
		}

		$this->registerBaseProviders();
		$this->registerConfiguredProviders();

		$this->app = Bridge::create($this->container);
		$this->booted = true;
	}


	private function registerBaseProviders(): void
	{
		$this->registerEnvironment();
		$this->registerConfiguration();

		$providers = [
			new LogProvider($this),
		];

		array_map(fn($provider) => $provider->register(), $providers);
	}


	private function registerConfiguredProviders(): void
	{
		/** @var ConfigRepository $configs */
		$configs = $this->resolve(ConfigRepository::class);

		$providers = $configs->get("app.providers", []);
		$providers = array_filter($providers, fn($class) => is_a($class, ProviderInterface::class, true));
		$providers = array_map(fn($classname) => new $classname($this), $providers);

		array_map(fn($provider) => $provider->register(), $providers);
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


	public function getEnvironment(): EnvironmentInterface
	{
		return $this->environment;
	}


	public function getCache(): PhpFileCache
	{
		return $this->cache;
	}


	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}


	public function isDevelopment(): bool
	{
		return $this->environment->get("APP_ENV") === "local";
	}


	public function isTesting(): bool
	{
		return $this->environment->get("APP_ENV") === "testing";
	}


	public function isProduction(): bool
	{
		return ! $this->isDevelopment() && ! $this->isTesting();
	}


	public function run(): void
	{
		$this->app->run();
	}
}