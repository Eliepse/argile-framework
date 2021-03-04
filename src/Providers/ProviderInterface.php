<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\Core\Application;

interface ProviderInterface
{
	public function __construct(Application $app);


	public function register(): void;


	public function boot(): void;
}