<?php

namespace Eliepse\Argile\Tests;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected Logger $logger;


	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$stream = new RotatingFileHandler(__DIR__ . "/logs/log.log", 7, Logger::DEBUG);
		$stream->setFormatter(
			new LineFormatter(
				"[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
				"Y-m-d H:i:s"
			)
		);
		$this->logger = new Logger("tests", [$stream]);
	}
}