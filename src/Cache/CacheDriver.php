<?php

namespace Eliepse\Argile\Cache;

interface CacheDriver
{
	public function __construct(string $name, array $config = []);
}