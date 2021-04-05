<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Core\Application;

abstract class ServiceProvider implements ProviderInterface
{
	public function __construct(protected Application $app) { }
}