<?php

namespace Eliepse\Argile\View\Loaders;

use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewLoader extends Loader
{
	public function __construct(private string $viewPath) { }


	public function load(TemplateReferenceInterface $template): Storage
	{
		$path = $this->viewPath . $template->getPath();

		if (file_exists($path)) {
			return new FileStorage($path);
		}

		throw new \ErrorException("Unable to access the view file for: " . $template->getLogicalName());
	}


	public function isFresh(TemplateReferenceInterface $template, int $time): bool
	{
		return true;
	}


	public function getBasePath(): string
	{
		return $this->viewPath;
	}
}