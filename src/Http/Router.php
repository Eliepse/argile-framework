<?php

namespace Eliepse\Argile\Http;

use Eliepse\Argile\Core\Application;
use Eliepse\Argile\Http\Controllers\BuildtimeController;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;

final class Router
{
	/** @var array */
	static private array $builtimeRoutes = [];


	static public function get(string $pattern, callable|array|string $callable): RouteInterface
	{
		$route = Application::getInstance()->getSlim()->get($pattern, $callable);
		Router::rememberBuildtimeRoute($route);
		return $route;
	}


	static public function post(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->post($pattern, $callable);
	}


	static public function put(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->put($pattern, $callable);
	}


	static public function patch(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->patch($pattern, $callable);
	}


	static public function delete(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->delete($pattern, $callable);
	}


	static public function options(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->options($pattern, $callable);
	}


	static public function any(string $pattern, callable|array|string $callable): RouteInterface
	{
		return Application::getInstance()->getSlim()->any($pattern, $callable);
	}


	static public function group(string $pattern, callable|array|string $callable): RouteGroupInterface
	{
		return Application::getInstance()->getSlim()->group($pattern, $callable);
	}


	static public function redirect(string $from, $to, int $status = 302): RouteInterface
	{
		return Application::getInstance()->getSlim()->redirect($from, $to, $status);
	}


	/**
	 * Check if the given route is a type of route compiled at buildtime,
	 * and keep its identifier in memory if true for later check.
	 *
	 * @param RouteInterface $route
	 */
	static private function rememberBuildtimeRoute(RouteInterface $route): void
	{
		$callable = $route->getCallable();

		if (is_string($callable) && is_a($callable, BuildtimeController::class, true)) {
			Router::$builtimeRoutes[$route->getIdentifier()] = $route;
		}

		if (is_array($callable) && is_a($callable[0], BuildtimeController::class, true)) {
			Router::$builtimeRoutes[$route->getIdentifier()] = $route;
		}
	}


	/**
	 * Returns the identifiers of routes that are compiled at buildtime
	 * and that has been kept in memory. Buildtime routes are not kept
	 * in memory if not set through this Router.
	 *
	 * @return string[]
	 */
	static public function getBuildtimRoutes(): array
	{
		return Router::$builtimeRoutes;
	}


	/**
	 * Check if a route is a type of route compiled at buildtime.
	 * This method prevent checking the filesystem if the route
	 * has been compiled or not.
	 *
	 * @param RouteInterface $route
	 *
	 * @return bool
	 */
	static public function isBuildtimeRoute(RouteInterface $route): bool
	{
		return array_key_exists($route->getIdentifier(), Router::$builtimeRoutes);
	}


    /**
     * Clear routes from memory.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$builtimeRoutes = [];
    }
}