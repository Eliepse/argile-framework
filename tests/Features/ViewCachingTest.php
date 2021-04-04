<?php

namespace Eliepse\Argile\Tests\Features;

use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Config\ConfigRepository;
use Eliepse\Argile\Tests\TestCase;
use Eliepse\Argile\View\ViewFactory;

final class ViewCachingTest extends TestCase
{
	private ConfigRepository $configs;
	private ViewFactory $factory;
	private Cache $cache;


	protected function setUp(): void
	{
		parent::setUp();
		$this->configs = $this->app->resolve(ConfigRepository::class);
		$this->factory = $this->app->resolve(ViewFactory::class);
		$this->cache = $this->factory->getLoaders()["cache"]->getCache();
	}


	public function testCacheInactive(): void
	{
		$this->configs->set("view.cache.enable", false);
		$view = "hello";
		$ref = $this->factory->getViewReference($view);
		$this->factory->render($view);
		$this->assertFalse($this->cache->contains($ref->getLogicalName()));
	}


	public function testCacheView(): void
	{
		$this->configs->set("view.cache.enable", true);
		$view = "hello";
		$ref = $this->factory->getViewReference($view);
		$this->factory->render($view);
		$this->assertTrue($this->cache->contains($ref->getLogicalName()));
	}


	public function testCacheNestedViews(): void
	{
		$this->configs->set("view.cache.enable", true);
		$view = "village";
		$rootRef = $this->factory->getViewReference($view);
		$childRef = $this->factory->getViewReference("villageBuilding");
		$this->factory->render($view, ["buildings" => [1 => "church", 3 => "house"]]);
		$this->assertTrue($this->cache->contains($rootRef->getLogicalName()));
		$this->assertTrue($this->cache->contains($childRef->getLogicalName()));
	}
}