<?php

namespace Eliepse\Argile\Core;

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use ErrorException;

final class Environment implements EnvironmentInterface
{
	protected function __construct(private RepositoryInterface $repository)
	{
		$this->validate("APP_ENV", ["required" => true, "in" => ['local', 'production', 'testing']]);
		$this->validate("APP_ONLINE", ["required" => true, "empty" => false]);
		$this->validate("APP_SESSION_PREFIX", ["empty" => false]);
		$this->validate("APP_CACHE_PREFIX", ["empty" => false]);
	}


	static public function createFromArray(array $envs): self
	{
		$repository = RepositoryBuilder::createWithDefaultAdapters()->immutable()->make();

		foreach ($envs as $key => $value) {
			$repository->set($key, $value);
		}

		return new self($repository);
	}


	static public function createMutableFromArray(array $envs): self
	{
		$repository = RepositoryBuilder::createWithDefaultAdapters()->make();

		foreach ($envs as $key => $value) {
			$repository->set($key, $value);
		}

		return new self($repository);
	}


	static public function createFromFile(string $env_dir): self
	{
		$repository = RepositoryBuilder::createWithDefaultAdapters()->immutable()->make();
		Dotenv::create($repository, $env_dir)->load();
		return new self($repository);
	}


	public function has(string $key): bool
	{
		return $this->repository->has($key);
	}


	public function get(string $key, $default = null): mixed
	{
		if (! $this->repository->has($key)) {
			return $default;
		}

		$value = $this->repository->get($key);

		if (! is_string($value)) {
			return $value;
		}

		switch (strtolower($value)) {
			case 'true':
				return true;
			case 'false':
				return false;
			case 'null':
				return null;
		}

		return $value;
	}


	/**
	 * @param string $key
	 * @param array<string, mixed> $rules
	 * @param bool $throw
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	public function validate(string $key, array $rules, bool $throw = true): bool
	{
		$required = $rules['required'] ?? false;
		$empty = $rules['empty'] ?? true;

		if ($required && ! $this->has($key)) {
			if ($throw) {
				throw new ErrorException("The '$key' environment variable is required.");
			}
			return false;
		}

		$value = $this->get($key);

		if (! $empty && $this->has($key) && empty($value)) {
			if ($throw) {
				throw new ErrorException("The '$key' environment cannot be empty.");
			}
			return false;
		}

		if (isset($rules['in']) && ! in_array($value, $rules['in'])) {
			if ($throw) {
				throw new ErrorException("The '$key' environment must be one of: " . join(', ', $rules['in']));
			}
			return false;
		}

		if ($rules['type'] ?? false) {
			if ('integer' === $rules['type'] && ! ctype_digit(strval($value))) {
				if ($throw) {
					throw new ErrorException("The '$key' environment must be an integer.");
				}
				return false;
			}

			if ('boolean' === $rules['type'] && (! is_bool($value) || in_array($value, ["true", "false"]))) {
				if ($throw) {
					throw new ErrorException("The '$key' environment must be a boolean.");
				}
				return false;
			}
		}

		return true;
	}


	public function getRepository(): RepositoryInterface
	{
		return $this->repository;
	}
}