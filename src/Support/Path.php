<?php

namespace Eliepse\Argile\Support;

use Eliepse\Argile\Core\Application;

final class Path
{
	public static function root(string $path = ""): string
	{
		return Application::getInstance()->getProjectDirectory() . '/' . $path;
	}


	public static function public(string $path = ""): string
	{
		return self::root('public/' . $path);
	}


	public static function storage(string $path = ""): string
	{
		return self::root('storage/' . $path);
	}


	public static function resources(string $path = ""): string
	{
		return self::root('resources/' . $path);
	}
}