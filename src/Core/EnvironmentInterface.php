<?php

namespace Eliepse\Argile\Core;

interface EnvironmentInterface
{

	public function has(string $key): bool;


	public function get(string $key, $default = null): mixed;


	public function validate(string $key, array $rules, bool $throw = true): bool;
}