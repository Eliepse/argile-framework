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
	 * Render the view with the App's default template engine
	 *
	 * @param string $viewName The name of the view
	 * @param array<string, mixed> $values The parameters to pass to the view
	 *
	 * @return string The rendered template
	 */
	public function render(string $viewName, array $values = []): string
	{
		// Add file extension if not set
		$name = pathinfo($viewName, PATHINFO_EXTENSION) ? $viewName : "$viewName.view";
		return $this->engine->render($name, $values);
	}


	/**
	 * Render the view with the App's default template engine
	 *
	 * @param string $viewName The name of the view
	 * @param array<string, mixed> $values The parameters to pass to the view
	 *
	 * @return string The rendered template
	 */
	public static function make(string $viewName, array $values = []): string
	{
		return self::getInstance()->render($viewName, $values);
	}
}