<?php

namespace Eliepse\Argile\Config;

final class Configuration
{
	/**
	 * Configuration constructor.
	 *
	 * @param string $namespace
	 * @param array<string, mixed> $configs
	 */
	public function __construct(
		private string $namespace,
		private array $configs,
	)
	{
		//
	}


	/**
	 * @param string $key The path key to the configuration dot formated (ex: view.compiled.path)
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		/** @var string[] $nodeNames */
		$nodeNames = explode(".", $key);

		if (! isset($this->configs[$nodeNames[0]])) {
			return $default;
		}

		$node = $this->configs[$nodeNames[0]];

		if (count($nodeNames) === 1) {
			return $node;
		}

		if (! is_array($node)) {
			return $default;
		}

		array_shift($nodeNames);

		$lastKey = array_key_last($nodeNames);

		foreach ($nodeNames as $key => $nodeName) {
			if (! $key !== $lastKey && ! is_array($node)) {
				return $default;
			}
			$node = isset($node[$nodeName]) ? $node[$nodeName] : $default;
		}

		return $node;
	}


	public function getNamespace(): string
	{
		return $this->namespace;
	}


}