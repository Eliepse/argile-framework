<?php

namespace Eliepse\Argile\View;

use Eliepse\Argile\App;
use Symfony\Component\Templating\EngineInterface;

final class ViewFactory
{
	static private ?ViewFactory $_instance = null;

	private EngineInterface $engine;


	public function __construct(EngineInterface $templateEngine = null)
	{
		$this->engine = $templateEngine ?? App::getInstance()->getTemplateEngine();
	}


	static public function getInstance(EngineInterface $templateEngine = null): ViewFactory
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self($templateEngine);
		}

		return self::$_instance;
	}


	/**
	 * @param string $viewName
	 * @param array<string, mixed> $values
	 *
	 * @return string
	 */
	public function render(string $viewName, array $values = []): string
	{
		// Add file extension if not set
		$name = pathinfo($viewName, PATHINFO_EXTENSION) ? $viewName : "$viewName.view";
		return $this->engine->render($name, $values);
	}


	/**
	 * @param string $name
	 * @param array<string, mixed> $values
	 *
	 * @return string
	 */
	public static function make(string $name, array $values = []): string
	{
		return self::getInstance()->render($name, $values);
	}
}