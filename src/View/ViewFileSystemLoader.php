<?php

namespace Eliepse\Argile\View;

use Eliepse\Argile\Support\Environment;
use Eliepse\Argile\Support\Path;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewFileSystemLoader extends FilesystemLoader
{
	private string $cachePath;


	public function __construct($templatePathPatterns)
	{
		parent::__construct($templatePathPatterns);
		$this->cachePath = Path::storage("framework/views/");
	}


	public function load(TemplateReferenceInterface $template)
	{
		$file = $template->get('name');

		if (self::isAbsolutePath($file) && is_file($file)) {
			return new FileStorage($file);
		}

		$replacements = [];
		foreach ($template->all() as $key => $value) {
			$replacements[ '%' . $key . '%' ] = $value;
		}

		$templateFailures = [];
		foreach ($this->templatePathPatterns as $templatePathPattern) {
			if (is_file($view_path = strtr($templatePathPattern, $replacements)) && is_readable($view_path)) {

				$cache_path = $this->getCachePath($template);

				if ($this->isCached($cache_path, $view_path)) {
					return new FileStorage($cache_path);
				}

				$content = $this->parseTemplate(new FileStorage($view_path));

				if (Environment::isProduction()) {
					$this->cacheView($template, $content);
				}

				return new StringStorage($content);
			}

			if (null !== $this->logger) {
				$templateFailures[] = $template;
			}
		}

		// only log failures if no template could be loaded at all
		foreach ($templateFailures as $temp) {
			if (null !== $this->logger) {
				$this->logger->debug('Failed loading template file.', [
					'file' => $temp->get('name'),
				]);
			}
		}

		return false;
	}


	private function getCachePath(TemplateReferenceInterface $template): string
	{
		return $this->cachePath . md5($template->get("name")) . ".php";
	}


	private function parseTemplate(Storage $file): string
	{
		$content = $file->getContent();
		$parsed_content = preg_replace_callback("/({([{%#])\s*(.+)\s*[}%#]})/miU", function ($matches) {
			switch ($matches[2]) {
				case '{':
					return '<?= $view->escape(' . trim($matches[3]) . ') ?>';
				case '%':
					return $this->parseLogicalBrackets($matches[3]);
				case '#':
					return "<?php # $matches[3] ?>";
			}
			return $matches[0];
		}, $content);

		if (is_null($parsed_content)) {
			throw new \ErrorException("Unable to parse the view, error with the 'preg_replace_callback'.");
		}

		return $parsed_content;
	}


	private function parseLogicalBrackets(string $content): string
	{
		$parsed_bracket = preg_replace_callback("/(\s*([a-z]+)\s*(.*))/mi", function ($matches) {
			switch ($matches[2]) {
				case 'include':
					return '<?= $view->render(' . trim($matches[3]) . ') ?>';
				case 'if':
					return '<?php if(' . trim($matches[3]) . '): ?>';
				case 'endif':
					return '<?php endif; ?>';
				case 'for':
					return '<?php foreach(' . trim($matches[3]) . '): ?>';
				case 'endfor':
					return '<?php endforeach; ?>';
			}
			return "";
		}, $content);

		if (is_null($parsed_bracket)) {
			throw new \ErrorException("Unable to parse logical bracket.");
		}

		return $parsed_bracket;
	}


	private function isCached(string $cache_path, string $view_path): bool
	{
		return Environment::isProduction() && is_file($cache_path) && filemtime($view_path) < filemtime($cache_path);
	}


	private function cacheView(TemplateReferenceInterface $template, string $content): void
	{
		$cache_path = $this->getCachePath($template);

		if (! is_dir($this->cachePath)) {
			if (false === mkdir($this->cachePath, 0774, true)) {
				/** @phpstan-ignore-next-line */
				$this->logger->error("Could not create views cache directory.", [
					"view" => $template->get('name'),
					"cachePath" => $cache_path,
				]);
			}
		}

		if (false === file_put_contents($this->getCachePath($template), $content)) {
			/** @phpstan-ignore-next-line */
			$this->logger->error("Failed to write parsed template to cache.", [
				"view" => $template->get('name'),
				"cachePath" => $cache_path,
			]);
		}
	}

}