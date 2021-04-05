<?php

namespace Eliepse\Argile\View\Loaders;

use League\Flysystem\Filesystem;
use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewStaticLoader extends Loader
{
	public static string $pathSuffix = "/static/";

	public function __construct(private Filesystem $fs)
	{
		//
	}


	public function load(TemplateReferenceInterface $template): Storage
	{
		$path = self::$pathSuffix . $template->getPath();

		if ($this->fs->fileExists($path)) {
			return new FileStorage($path);
		}

		throw new \ErrorException("Unable to access the *static* view file for: " . $template->getLogicalName());
	}


	public function getHashedFilename(TemplateReferenceInterface $template): string
	{
		return hash('sha256', $template->getLogicalName());
	}


	public function isFresh(TemplateReferenceInterface $template, int $time): bool
	{
		return true;
	}


	public function saveTemplate(TemplateReferenceInterface $template, Storage $content): void
	{
		$this->fs->write(self::$pathSuffix . $this->getHashedFilename($template), $content->getContent());
	}
}