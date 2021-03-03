<?php

namespace Eliepse\Argile;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\PhpFileCache;
use Eliepse\Argile\Providers\LogServiceProvider;
use Eliepse\Argile\Providers\ProviderInterface;
use Eliepse\Argile\Providers\ViewServiceProvider;
use Eliepse\Argile\Support\Environment;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\ViewFileSystemLoader;
use ErrorException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use function DI\factory as DIFactory;

final class App
{
	private static self $_instance;
	private string $project_directory;

	private \Slim\App $app;
	private PhpEngine $templating;
	private PhpFileCache $cache;
	private Logger $logger;
	public Container $container;

	/**
	 * @var string[]
	 */
	public static array $defaultProviders = [
		LogServiceProvider::class,
		ViewServiceProvider::class,
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
		self::$_instance = new self($projectRoot);
		Environment::load(Path::root());
		return self::$_instance;
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
	 * @return App
	 * @throws ErrorException
	 */
	public static function getInstance(): self
	{
		if (empty(self::$_instance))
			throw new ErrorException(self::class . "has not been initialized.");
		return self::$_instance;
	}


	public function getProjectDirectory(): string
	{
		return $this->project_directory;
	}


	public function loadCacheSystem(): void
	{
		$this->cache = new PhpFileCache(Path::storage("framework/cache"));
		$this->cache->setNamespace(Environment::get("APP_CACHE_PREFIX", "simpleApp_"));
	}


	public function getTemplatingEngine(): PhpEngine
	{
		$viewCachePath = Environment::isProduction() ? Path::storage("framework/views/") : null;
		$filesystem = new ViewFileSystemLoader([Path::resources("views/%name%")], $viewCachePath);
		$filesystem->setLogger($this->logger);
		return new PhpEngine(new TemplateNameParser(), $filesystem);
	}


	public function getSlim(): \Slim\App
	{
		return $this->app;
	}


	public function getTemplateEngine(): EngineInterface
	{
		return $this->templating;
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