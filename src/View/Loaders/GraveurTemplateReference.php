<?php

namespace Eliepse\Argile\View\Loaders;

use Symfony\Component\Templating\TemplateReferenceInterface;

final class GraveurTemplateReference implements TemplateReferenceInterface
{
	private array $parameters;


	/**
	 * GraveurTemplateReference constructor.
	 *
	 * @param string $viewKey The path of the view as a 'dotted' format (ex: "parts.widget.map")
	 */
	public function __construct(string $viewKey)
	{
		// We clean the given key from extra dots or extension
		// so we can have a standarized version. This is espacially
		// usefull to get a consistent hashed name for cache and static.
		$cleanedKey = trim($viewKey, ". \t\n\r\0\x0B");
		if (str_ends_with($cleanedKey, ".view")) {
			$cleanedKey = substr($cleanedKey, 0, strlen($cleanedKey) - 5);
		}

		$pathElements = explode(".", $cleanedKey);

		$this->parameters = [
			"name" => array_pop($pathElements),
			"path" => $cleanedKey,
			"extension" => "view",
		];
	}


	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		return $this->parameters;
	}


	/**
	 * @inheritDoc
	 */
	public function set(string $name, string $value): self
	{
		if (\array_key_exists($name, $this->parameters)) {
			$this->parameters[$name] = $value;
		} else {
			throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
		}

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function get(string $name): mixed
	{
		if (\array_key_exists($name, $this->parameters)) {
			return $this->parameters[$name];
		}

		throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
	}


	/**
	 * @inheritDoc
	 */
	public function getPath(): string
	{
		return str_replace(".", DIRECTORY_SEPARATOR, $this->parameters["path"]) . "." . $this->parameters["extension"];
	}


	/**
	 * @inheritDoc
	 */
	public function getLogicalName(): string
	{
		return $this->parameters["path"];
	}


	/**
	 * @inheritDoc
	 */
	public function __toString(): string
	{
		return $this->getLogicalName();
	}
}