<?php

namespace Eliepse\Argile\Core;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\PhpFileCache;
use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Errors\HtmlErrorRenderer;
use Eliepse\Argile\Errors\JsonErrorRendered;
use Eliepse\Argile\Errors\PlainTextErrorRenderer;
use Eliepse\Argile\Errors\XmlErrorRenderer;
use Eliepse\Argile\Providers\LogProvider;
use Eliepse\Argile\Providers\ProviderInterface;
use Eliepse\Argile\Support\Config;
use ErrorException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use function DI\factory as DIFactory;

final class Application
{
	static private self $_instance;

	private string $project_directory;
	private string $environmentPath;
	private string $configPath;
	private \Slim\App $app;
	private PhpFileCache $cache;
	private bool $booted = false;
	private Environment $environment;

	public Container $container;


	/** @noinspection PhpIncludeInspection */
	private function __construct(string $project_directory)
	{
		$this->project_directory = $project_directory;
		$this->environmentPath = $project_directory;
		$this->configPath = $project_directory . "/configs/";

		$appEnv = $_ENV["APP_ENV"] ?? null;
		$envCachePath = $this->project_directory . "/bootstrap/cache/env.php";

		if ($appEnv === "testing") {
			$this->environment = Environment::createMutableFromArray($_ENV);
		} else if (is_file($envCachePath)) {
			$envs = include $envCachePath;
			$this->environment = Environment::createFromArray($envs);
		} else {
			$this->environment = Environment::createFromFile($this->environmentPath);
		}

		$builder = new ContainerBuilder();
		$builder->useAutowiring(true);
		$this->container = $builder->build();
		$this->container->set(Application::class, $this);
	}


	public static function init(string $projectRoot): self
	{
		return self::$_instance = new self($projectRoot);
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

		$this->environment = Environment::createMutableFromArray(array_merge($_ENV, $env));

		return $this;
	}


	private function registerEnvironment(): void
	{
		if (! $this->container->has(EnvironmentInterface::class)) {
			$this->register(EnvironmentInterface::class, function () {
				return $this->environment;
			});
		}

		if (! $this->container->has(Environment::class)) {
			$this->register(Environment::class, function (ContainerInterface $c) {
				return $c->get(EnvironmentInterface::class);
			});
		}
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
		$this->register(ConfigRepository::class, function (Application $app) {
			$cachePath = $app->project_directory . "/bootstrap/configs.php";

			if (is_file($cachePath)) {
				return new ConfigRepository($cachePath);
			}

			return new ConfigRepository($this->configPath);
		});
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


	public function getConfigPath(): string
	{
		return $this->configPath;
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


	private function addConfiguredMiddlewares(): void
	{
		$globalMiddlewares = Config::get("middlewares.global", []);

		foreach ($globalMiddlewares as $middlewareClass) {
			if (! is_a($middlewareClass, MiddlewareInterface::class, true)) {
				throw new ErrorException("Middlewares should implement Psr\Http\Server\MiddlewareInterface. Invalid middleware: $middlewareClass");
			}

			$this->app->addMiddleware($this->container->make($middlewareClass));
		}
	}


	public function run(): void
	{
		$this->addConfiguredMiddlewares();

		// Adding core middleware
		$this->app->addBodyParsingMiddleware();
		$this->app->addRoutingMiddleware();
		$errMiddleware = $this->app->addErrorMiddleware(
			! $this->isProduction(),
			true,
			true,
			$this->resolve(LoggerInterface::class)
		);

		$errMiddleware->getDefaultErrorHandler()->registerErrorRenderer("text/html", HtmlErrorRenderer::class);
		$errMiddleware->getDefaultErrorHandler()->registerErrorRenderer("application/json", JsonErrorRendered::class);
		$errMiddleware->getDefaultErrorHandler()->registerErrorRenderer("text/plain", PlainTextErrorRenderer::class);
		$errMiddleware->getDefaultErrorHandler()->registerErrorRenderer("application/xhtml+xml", XmlErrorRenderer::class);

		$this->app->run();
	}
}