<?php

namespace Eliepse\Argile\View\Loaders;

use Eliepse\Argile\Support\Env;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewCacheLoader extends Loader
{
	/**
	 * @var array<string, Storage>
	 */
	private array $runtimeCache = [];


	public function __construct(
		private Filesystem $filesystem,
		private string $cachePath
	)
	{
		//
	}


	public function load(TemplateReferenceInterface $template): ?Storage
	{
		$hashedName = $this->getHashedFilename($template);

		if (isset($this->runtimeCache[$hashedName])) {
			return new StringStorage($this->runtimeCache[$hashedName]);
		}

		$path = $this->cachePath . $hashedName;

		if (is_readable($path)) {
			return new FileStorage($path);
		}

		return null;
	}


	private function getHashedFilename(TemplateReferenceInterface $template): string
	{
		return hash('sha256', $template->getLogicalName());
	}


	public function isFresh(TemplateReferenceInterface $template, int $time): bool
	{
		return time() >= $time + Env::get("VIEW_CACHE_TTL", 7 * 24 * 3_600);
	}


	public function saveTemplate(TemplateReferenceInterface $template, Storage $content): void
	{
		$hashedName = $this->getHashedFilename($template);
		$this->runtimeCache[$hashedName] = $content->getContent();
		$this->filesystem->dumpFile($this->cachePath . $hashedName . ".php", $this->runtimeCache[$hashedName]);
	}


	public function getBasePath(): string
	{
		return $this->cachePath;
	}
}