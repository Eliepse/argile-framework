<?php

namespace Eliepse\Argile\Providers;

use Eliepse\Argile\App;

interface ProviderInterface
{
	public function __construct(App $app);


	public function register(): void;


	public function boot(): void;
}