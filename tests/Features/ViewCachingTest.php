<?php

namespace Eliepse\Argile\Tests\Features;

use Doctrine\Common\Cache\Cache;
use Eliepse\Argile\Core\Environment;
use Eliepse\Argile\Core\EnvironmentInterface;
use Eliepse\Argile\Tests\TestCase;
use Eliepse\Argile\View\ViewFactory;

final class ViewCachingTest extends TestCase
{
	private EnvironmentInterface|Environment $env;
	private ViewFactory $factory;
	private Cache $cache;


	protected function setUp(): void
	{
		parent::setUp();
		$this->env = $this->app->resolve(EnvironmentInterface::class);
		$this->env->getRepository()->set("VIEW_CACHE", true);
		$this->factory = $this->app->resolve(ViewFactory::class);
		$this->cache = $this->factory->getLoaders()["cache"]->getCache();
	}


	public function testCacheInactive(): void
	{
		$this->env->getRepository()->set("VIEW_CACHE", false);
		$view = "hello";
		$ref = $this->factory->getViewReference($view);
		$this->factory->render($view);
		$this->assertFalse($this->cache->contains($ref->getLogicalName()));
	}


	public function testCacheView(): void
	{
		$this->env->getRepository()->set("VIEW_CACHE", true);
		$view = "hello";
		$ref = $this->factory->getViewReference($view);
		$this->factory->render($view);
		$this->assertTrue($this->cache->contains($ref->getLogicalName()));
	}


	public function testCacheNestedViews(): void
	{
		$this->env->getRepository()->set("VIEW_CACHE", true);
		$view = "village";
		$rootRef = $this->factory->getViewReference($view);
		$childRef = $this->factory->getViewReference("villageBuilding");
		$this->factory->render($view, ["buildings" => [1 => "church", 3 => "house"]]);
		$this->assertTrue($this->cache->contains($rootRef->getLogicalName()));
		$this->assertTrue($this->cache->contains($childRef->getLogicalName()));
	}
}