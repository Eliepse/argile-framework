<?php

namespace Eliepse\Argile\View;

use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewFileSystemLoader extends FilesystemLoader
{
	private ?string $cachePath;


	public function __construct($templatePathPatterns, string $cachePath = null)
	{
		parent::__construct($templatePathPatterns);
		$this->cachePath = $cachePath;
	}


	/**
	 * @param TemplateReferenceInterface $template
	 *
	 * @return FileStorage|StringStorage|false
	 * @throws \ErrorException
	 */
	public function load(TemplateReferenceInterface $template)
	{
		$file = $template->get('name');

		if (self::isAbsolutePath($file) && is_file($file)) {
			return new FileStorage($file);
		}

		$paths = $this->getTemplateFilePaths($template);

		if (empty($paths)) {
			$this->logger?->debug('Failed loading template.', [
				'name' => $template->get('name'),
			]);
			return false;
		}

		foreach ($paths as $templatePath) {
			$cache_path = $this->getCachePath($templatePath);

			if ($this->isCached($templatePath)) {
				return new FileStorage($cache_path);
			}

			$content = $this->parseTemplate(new FileStorage($templatePath));
			$this->cacheTemplate($templatePath, $content);
			return new StringStorage($content);
		}

		return false;
	}


	/**
	 * Resolve template path-patterns with the fiven template reference.
	 * Paths are then tested and filtered if the template is not found/readable.
	 *
	 * @param TemplateReferenceInterface $template
	 *
	 * @return string[]
	 */
	private function getTemplateFilePaths(TemplateReferenceInterface $template): array
	{
		$replacements = array_combine(
			array_map(fn($val) => "%$val%", array_keys($template->all())),
			array_values($template->all()),
		);

		$paths = array_map(
			function ($pathPattern) use ($replacements) {
				return strtr($pathPattern, $replacements);
			},
			$this->templatePathPatterns
		);

		return array_filter($paths, fn($path) => is_file($path) && is_readable($path));
	}


	/**
	 * @param string $templatePath The resolved template path
	 *
	 * @return string The theorical path to the cached template file
	 */
	private function getCachePath(string $templatePath): string
	{
		if (! $this->isCacheEnabled()) {
			return $templatePath;
		}

		return $this->cachePath . hash("sha256", $templatePath) . ".php";
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


	/**
	 * Check if the cache is enable. Returns false if the cachePath
	 * has not been set, or if the environment is not in Production.
	 *
	 * @return bool
	 */
	private function isCacheEnabled(): bool
	{
		return $this->cachePath !== null;
	}


	/**
	 * @param string $templatePath The resolved template path
	 *
	 * @return bool
	 */
	private function isCached(string $templatePath): bool
	{
		if (! $this->isCacheEnabled()) {
			return false;
		}

		$templateCachePath = $this->getCachePath($templatePath);

		return is_file($templatePath) && filemtime($templatePath) < filemtime($templatePath);
	}


	/**
	 * Write the cached template to the disk.
	 *
	 * @param string $templatePath The resolved path to the template file (not the cached file path)
	 * @param string $content The content of the cached template
	 */
	private function cacheTemplate(string $templatePath, string $content): void
	{
		if (! $this->isCacheEnabled()) {
			return;
		}

		$cachePath = $this->getCachePath($templatePath);

		if ($this->cachePath && ! is_dir($this->cachePath)) {
			if (false === mkdir($this->cachePath, 0774, true)) {
				$this->logger?->error("Could not create views cache directory.", [
					"templatePath" => $templatePath,
					"cachePath" => $cachePath,
				]);
			}
		}

		if (false === file_put_contents($cachePath, $content)) {
			$this->logger?->error("Failed to write parsed template to cache.", [
				"templatePath" => $templatePath,
				"cachePath" => $cachePath,
			]);
		}
	}

}