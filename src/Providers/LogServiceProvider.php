<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Support\Path;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LogServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->register(LoggerInterface::class, function () {
			$stream = new RotatingFileHandler($this->getLogDirectory(), 7, Logger::DEBUG);
			$stream->setFormatter(new LineFormatter(
				"[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
				"Y-m-d H:i:s"
			));

			$logger = new Logger("local");
			$logger->pushHandler($stream);
			return $logger;
		});
	}


	public function boot(): void { }


	protected function getLogDirectory(): string
	{
		return Path::storage("logs/log.log");
	}
}