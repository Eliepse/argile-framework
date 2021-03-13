<?php

namespace Eliepse\Argile\View\Loaders;

use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Support\Env;
use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ViewCacheLoader extends Loader
{
	/**
	 * @var array<string, Storage>
	 */
	private array $runtimeCache = [];


	public function __construct(private Cache $cache)
	{
		//
	}


	public function load(TemplateReferenceInterface $template): ?Storage
	{
		$key = $template->getLogicalName();

		if (isset($this->runtimeCache[$key])) {
			return new StringStorage($this->runtimeCache[$key]);
		}

		if ($this->cache->contains($key)) {
			return new StringStorage($this->cache->fetch($key));
		}

		return null;
	}


	public function isFresh(TemplateReferenceInterface $template, int $time): bool
	{
		return $this->cache->contains($template->getLogicalName())
			&& time() >= $time + Env::get("VIEW_CACHE_TTL", 7 * 24 * 3_600);
	}


	public function saveTemplate(TemplateReferenceInterface $template, Storage $content): void
	{
		$key = $template->getLogicalName();
		$value = $content->getContent();

		$this->runtimeCache[$key] = $value;
		$this->cache->save($key, $value);
	}


	public function getCache(): Cache
	{
		return $this->cache;
	}
}