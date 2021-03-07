<?php

namespace Eliepse\Argile\View\Loaders;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewStaticLoader extends Loader
{
	public function __construct(
		private Filesystem $filesystem,
		private string $staticPath
	)
	{
		//
	}


	public function load(TemplateReferenceInterface $template): Storage
	{
		$path = $this->staticPath . $template->getPath();

		if (is_readable($path)) {
			return new FileStorage($path);
		}

		throw new \ErrorException("Unable to access the *static* view file for: " . $template->getLogicalName());
	}


	private function getHashedFilename(TemplateReferenceInterface $template): string
	{
		return hash('sha256', $template->getLogicalName());
	}


	public function isFresh(TemplateReferenceInterface $template, int $time): bool
	{
		return true;
	}


	public function saveTemplate(TemplateReferenceInterface $template, Storage $content): void
	{
		$this->filesystem->dumpFile($this->staticPath . $this->getHashedFilename($template), $content->getContent());
	}


	public function getBasePath(): string
	{
		return $this->staticPath;
	}
}