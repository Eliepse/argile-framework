<?php

namespace Eliepse\Argile\View;

use Eliepse\Argile\Core\Application;
use Symfony\Component\Templating\EngineInterface;

final class ViewFactory
{
	private EngineInterface $engine;


	public function __construct(EngineInterface $templateEngine = null)
	{
		$this->engine = $templateEngine ?? Application::getInstance()->getTemplateEngine();
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
		$viewFactory = Application::getInstance()->container->get(self::class);
		return $viewFactory->render($viewName, $values);
	}
}