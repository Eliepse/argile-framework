<?php

namespace Eliepse\Argile\Testing;

use Eliepse\Argile\Repositories\EnvironmentRepository;

final class EnvironmentProvider extends \Eliepse\Argile\Providers\EnvironmentProvider
{
	protected function getRepository(): EnvironmentRepository
	{
		return EnvironmentRepository::createFromArray(getenv());
	}
}