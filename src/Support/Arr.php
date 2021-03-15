<?php

namespace Eliepse\Argile\Support;

final class Arr
{
	/**
	 * Get an item from an array using "dot" notation.
	 * Credit: https://github.com/laravel/framework
	 *
	 * @param array $array $array
	 * @param string|int|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function get(array $array, string|int $key = null, $default = null): mixed
	{
		if (! is_array($array)) {
			return $default;
		}

		if (is_null($key)) {
			return $array;
		}

		if (array_key_exists($key, $array)) {
			return $array[$key];
		}

		if (! str_contains($key, '.')) {
			return $array[$key] ?? $default;
		}

		foreach (explode('.', $key) as $segment) {
			if (is_array($array) && array_key_exists($segment, $array)) {
				$array = $array[$segment];
			} else {
				return $default;
			}
		}

		return $array;
	}


	/**
	 * Set an array item to a given value using "dot" notation.
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param array $array
	 * @param string|null $key
	 * @param mixed $value
	 *
	 * @return array
	 * @todo Improve performences by removing mofiable parameter
	 */
	public static function set(array $array, string|null $key, mixed $value): array
	{
		if (is_null($key)) {
			return $value;
		}

		$keys = explode('.', $key);
		$firstKey = array_shift($keys);

		if (! isset($array[$firstKey]) || ! is_array($array[$firstKey])) {
			$array[$firstKey] = [];
		}


		if (count($keys) > 0) {
			$array[$firstKey] = Arr::set($array[$firstKey], join(".", $keys), $value);
			return $array;
		}

		$array[$firstKey] = $value;
		return $array;

//		foreach ($keys as $i => $key) {
//			if (count($keys) === 1) {
//				break;
//			}
//
//			unset($keys[$i]);
//
//			// If the key doesn't exist at this depth, we will just create an empty array
//			// to hold the next value, allowing us to create the arrays to hold final
//			// values at the correct depth. Then we'll keep digging into the array.
//			if (! isset($array[$key]) || ! is_array($array[$key])) {
//				$array[$key] = [];
//			}
//
//			$array = &$array[$key];
//		}
//
//		$array[array_shift($keys)] = $value;
//
//		return $array;
	}
}