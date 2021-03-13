<?php

namespace Eliepse\Argile\Testing;

use Eliepse\Argile\Core\Environment;
use Eliepse\Argile\Core\EnvironmentInterface;

final class EnvironmentProvider extends \Eliepse\Argile\Providers\EnvironmentProvider
{
	protected function getRepository(): EnvironmentInterface
	{
		return Environment::createMutableFromArray(getenv());
	}
}