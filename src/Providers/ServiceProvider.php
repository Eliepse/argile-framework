<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\App;

abstract class ServiceProvider implements ProviderInterface
{


	public function __construct(protected App $app) { }
}