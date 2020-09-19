<?php

namespace Eliepse\Argile\Support;

use Error;
use ErrorException;

final class Asset
{
	/**
	 * @param string $asset_path
	 * @param string|null $fallback
	 *
	 * @return string
	 * @throws ErrorException
	 */
	public static function webpack(string $asset_path, ?string $fallback = null): string
	{
		$manifest_path = Path::public("manifest.json");

		if (! file_exists($manifest_path)) {
			if (! is_null($fallback)) return $fallback;
			throw new ErrorException("Weback generated manifest (public/manifest.json) not found at $manifest_path.");
		}

		if (false === $json = file_get_contents($manifest_path)) {
			throw new ErrorException("Error while reading Webpack manifest at: $manifest_path");
		}

		$manifest = json_decode($json, true);

		if (! array_key_exists($asset_path, $manifest)) {
			if (! is_null($fallback)) return $fallback;
			throw new Error("$asset_path not found in webpack generated manifest.");
		}

		return $manifest[ $asset_path ];
	}
}