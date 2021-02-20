<?php

namespace Eliepse\Argile;

use DI\Bridge\Slim\Bridge;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\PhpFileCache;
use Eliepse\Argile\Support\Environment;
use Eliepse\Argile\Support\Path;
use Eliepse\Argile\View\ViewFileSystemLoader;
use ErrorException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

final class App
{
	private static self $_instance;
	private string $project_directory;

	private \Slim\App $app;
	private PhpEngine $templating;
	private PhpFileCache $cache;
	private Logger $logger;
	public Container $container;


	private function __construct(string $project_directory)
	{
		if (! is_dir($project_directory)) {
			throw new ErrorException("The project directory is not a valid or does not exist ($project_directory).");
		}
		$this->project_directory = $project_directory;
	}


	public static function init(string $project_directory): self
	{
		self::$_instance = new self($project_directory);
		Environment::load(Path::root());
		return self::$_instance;
	}


	public function loadSlim(): void
	{
		$builder = new ContainerBuilder();
		$builder->useAutowiring(true);
		$builder->useAnnotations(false);
		$this->container = $builder->build();
		$this->app = Bridge::create($this->container);
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


	public function loadLoggerSystem(): void
	{
		$stream = new RotatingFileHandler(Path::storage("logs/log.log"), 7, Logger::DEBUG);
		$stream->setFormatter(new LineFormatter(
			"[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
			"Y-m-d H:i:s"
		));

		$this->logger = new Logger("local");
		$this->logger->pushHandler($stream);
	}


	public function loadCacheSystem(): void
	{
		$this->cache = new PhpFileCache(Path::storage("framework/cache"));
		$this->cache->setNamespace(Environment::get("APP_CACHE_PREFIX", "simpleApp_"));
	}


	public function loadTemplatingSystem(): void
	{
		$viewCachePath = Environment::isProduction() ? Path::storage("framework/views/") : null;
		$filesystem = new ViewFileSystemLoader([Path::resources("views/%name%")], $viewCachePath);
		$filesystem->setLogger($this->logger);
		$this->templating = new PhpEngine(new TemplateNameParser(), $filesystem);
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