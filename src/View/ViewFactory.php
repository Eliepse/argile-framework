<?php

namespace Eliepse\Argile\View;

use Eliepse\Argile\Config\ConfigurationManager;
use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Support\Env;
use Eliepse\Argile\View\Loaders\GraveurTemplateReference;
use Eliepse\Argile\View\Loaders\ViewCacheLoader;
use Eliepse\Argile\View\Loaders\ViewStaticLoader;
use Eliepse\Argile\View\Parsers\ParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewFactory
{
	/** @var array<string, callable> */
	private array $escapers;

	/** @var array<string, array<string, callable>> */
	private array $escaperCache = [];


	public function __construct(
		private ViewStaticLoader $staticLoader,
		private ViewCacheLoader $cacheLoader,
		private LoaderInterface $viewLoader,
		private ParserInterface $parser,
		private LoggerInterface $logger,
		private ConfigurationManager $configs,
	)
	{
		// This initialization of escapers has been copied from
		// the PhpEngine of Symfony's Templating package
		$flags = \ENT_QUOTES | \ENT_SUBSTITUTE;
		$this->escapers = [
			'html' =>
			/**
			 * Runs the PHP function htmlspecialchars on the value passed.
			 *
			 * @param string $value The value to escape
			 *
			 * @return string the escaped value
			 */
				function ($value) use ($flags) {
					// Numbers and Boolean values get turned into strings which can cause problems
					// with type comparisons (e.g. === or is_int() etc).
					return \is_string($value) ? htmlspecialchars($value, $flags, "UTF-8", false) : $value;
				},

			'js' =>
			/**
			 * A function that escape all non-alphanumeric characters
			 * into their \xHH or \uHHHH representations.
			 *
			 * @param string $value The value to escape
			 *
			 * @return string the escaped value
			 */
				function ($value) {
					$callback = function ($matches) {
						$char = $matches[0];

						// \xHH
						if (! isset($char[1])) {
							return '\\x' . substr('00' . bin2hex($char), -2);
						}

						// \uHHHH
						$char = iconv('UTF-8', 'UTF-16BE', $char);

						return '\\u' . substr('0000' . bin2hex($char), -4);
					};

					if (null === $value = preg_replace_callback('#[^\p{L}\p{N} ]#u', $callback, $value)) {
						throw new \InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
					}

					return $value;
				},
		];
	}


	/**
	 * Render the view with the App's default template engine
	 *
	 * @param string $viewName The name of the view
	 * @param array<string, mixed> $parameters The parameters to pass to the view
	 *
	 * @return string The rendered template
	 * @throws \ErrorException
	 */
	public function render(string $viewName, array $parameters = []): string
	{
		$template = $this->getViewReference($viewName);

		// Support view compilation at buildtime
		if ($this->isCompiledViewEnabled() && $this->isCompiledTemplate($template)) {
			return $this->staticLoader->load($template);
		}

		// Support view caching
		if ($this->isCachedViewEnabled()) {
			// For now, we don't support cache revalidation
			// TODO: support cache revalidation
			if (is_null($parsedTemplate = $this->cacheLoader->load($template))) {
				$parsedTemplate = $this->parser->parse($this->viewLoader->load($template));
				$this->cacheLoader->saveTemplate($template, $parsedTemplate);
			}

			return $this->evaluate($parsedTemplate, $parameters);
		}

		// Default (but not optimal rendering)
		return $this->evaluate(
			$this->parser->parse(
				$this->viewLoader->load($template)
			),
			$parameters
		);
	}


	private function evaluate(Storage $template, array $parameters = []): ?string
	{
		$view = $this;

		if ($template instanceof FileStorage) {
			extract($parameters, \EXTR_SKIP);
			ob_start();
			/** @noinspection PhpIncludeInspection */
			require $template;
			return ob_get_clean();
		}

		if ($template instanceof StringStorage) {
			extract($parameters, \EXTR_SKIP);
			ob_start();
			eval('; ?>' . $template . '<?php ;');
			return ob_get_clean();
		}

		return null;
	}


	/**
	 * Escapes a string by using the current charset.
	 *
	 * @param mixed $value A variable to escape
	 * @param string $context
	 *
	 * @return mixed The escaped value
	 */
	public function escape(mixed $value, string $context = 'html'): mixed
	{
		if (is_numeric($value)) {
			return $value;
		}

		// If we deal with a scalar value, we can cache the result to increase
		// the performance when the same value is escaped multiple times (e.g. loops)
		if (is_scalar($value)) {
			if (! isset($this->escaperCache[$context][$value])) {
				$this->escaperCache[$context][$value] = $this->getEscaper($context)($value);
			}

			return $this->escaperCache[$context][$value];
		}

		return $this->getEscaper($context)($value);
	}


	/**
	 * Adds an escaper for the given context.
	 *
	 * @param string $context
	 * @param callable $escaper
	 */
	public function setEscaper(string $context, callable $escaper)
	{
		$this->escapers[$context] = $escaper;
		$this->escaperCache[$context] = [];
	}


	/**
	 * Gets an escaper for a given context.
	 *
	 * @param string $context
	 *
	 * @return callable|\Closure A PHP callable
	 */
	public function getEscaper(string $context): callable|\Closure
	{
		if (! isset($this->escapers[$context])) {
			throw new \InvalidArgumentException(sprintf('No registered escaper for context "%s".', $context));
		}

		return $this->escapers[$context];
	}


	/**
	 * @return array
	 */
	public function getLoaders(): array
	{
		return [
			"view" => $this->viewLoader,
			"cache" => $this->cacheLoader,
			"static" => $this->staticLoader,
		];
	}


	public function getViewReference(string $viewName): TemplateReferenceInterface
	{
		return new GraveurTemplateReference($viewName);
	}


	private function isCompiledViewEnabled(): bool
	{
		return $this->configs->get("view")->get("compile.enable");
	}


	private function isCompiledTemplate(string $viewName): bool
	{
		return false;
	}


	private function isCachedViewEnabled(): bool
	{
		return $this->configs->get("view")->get("cache.enable");
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
		$viewFactory = Application::getInstance()->resolve(ViewFactory::class);
		return $viewFactory->render($viewName, $values);
	}
}