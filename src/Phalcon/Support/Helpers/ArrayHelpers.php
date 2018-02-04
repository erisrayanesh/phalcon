<?php

function array_dot($array, $prepend = '')
{
	$results = [];

	foreach ($array as $key => $value) {
		if (is_array($value) && ! empty($value)) {
			$results = array_merge($results, array_dot($value, $prepend.$key.'.'));
		} else {
			$results[$prepend.$key] = $value;
		}
	}

	return $results;
}

function array_get($array, $key, $default = null)
{
	if (! array_accessible($array)) {
		return value($default);
	}

	if (is_null($key)) {
		return $array;
	}

	if (array_exists($array, $key)) {
		return $array[$key];
	}

	if (strpos($key, '.') === false) {
		return value($default);
	}

	foreach (explode('.', $key) as $segment) {
		if (array_accessible($array) && array_exists($array, $segment)) {
			$array = $array[$segment];
		} else {
			return value($default);
		}
	}

	return $array;
}

function array_pull(&$array, $key, $default = null)
{
	$value = array_get($array, $key, $default);
	array_forget($array, $key);
	return $value;
}

function array_prepend($array, $value, $key = null)
{
	if (is_null($key)) {
		array_unshift($array, $value);
	} else {
		$array = [$key => $value] + $array;
	}
	return $array;
}

function array_forget(&$array, $keys)
{
	$original = &$array;
	$keys = (array) $keys;

	if (count($keys) === 0) {
		return;
	}

	foreach ($keys as $key) {
		// if the exact key exists in the top-level, remove it
		if (array_exists($array, $key)) {
			unset($array[$key]);
			continue;
		}
		$parts = explode('.', $key);
		// clean up before each pass
		$array = &$original;
		while (count($parts) > 1) {
			$part = array_shift($parts);
			if (isset($array[$part]) && is_array($array[$part])) {
				$array = &$array[$part];
			} else {
				continue 2;
			}
		}
		unset($array[array_shift($parts)]);
	}
}

function array_pluck($array, $value, $key = null)
{
	$results = [];

	$value = is_string($value) ? explode('.', $value) : $value;
	$key = is_null($key) || is_array($key) ? $key : explode('.', $key);

	foreach ($array as $item) {
		$itemValue = data_get($item, $value);
		// If the key is "null", we will just append the value to the array and keep
		// looping. Otherwise we will key the array using the value of the key we
		// received from the developer. Then we'll return the final array form.
		if (is_null($key)) {
			$results[] = $itemValue;
		} else {
			$itemKey = data_get($item, $key);
			if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
				$itemKey = (string) $itemKey;
			}
			$results[$itemKey] = $itemValue;
		}
	}
	return $results;
}

function array_collapse($array)
{
	$results = [];
	foreach ($array as $values) {
		if ($values instanceof \Phalcon\Support\Collection) {
			$values = $values->all();
		} elseif (! is_array($values)) {
			continue;
		}
		$results = array_merge($results, $values);
	}
	return $results;
}

function array_exists($array, $key)
{
	if ($array instanceof \ArrayAccess) {
		return $array->offsetExists($key);
	}
	return array_key_exists($key, $array);
}

function array_accessible($value)
{
	return is_array($value) || $value instanceof \ArrayAccess;
}

function array_first(callable $callback = null, $default = null)
{
	if (is_null($callback)) {
		if (empty($array)) {
			return value($default);
		}
		foreach ($array as $item) {
			return $item;
		}
	}
	foreach ($array as $key => $value) {
		if (call_user_func($callback, $value, $key)) {
			return $value;
		}
	}

	return value($default);
}

function array_last($array, callable $callback = null, $default = null)
{
	if (is_null($callback)) {
		return empty($array) ? value($default) : end($array);
	}
	return array_first(array_reverse($array, true), $callback, $default);
}

function array_only($array, $keys)
{
	return array_intersect_key($array, array_flip((array) $keys));
}

function array_except($array, $keys)
{
	array_forget($array, $keys);
	return $array;
}

function array_where($array, callable $callback)
{
	return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
}

if (! function_exists('data_get')) {
	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param  mixed   $target
	 * @param  string|array  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function data_get($target, $key, $default = null)
	{
		if (is_null($key)) {
			return $target;
		}
		$key = is_array($key) ? $key : explode('.', $key);
		while (! is_null($segment = array_shift($key))) {
			if ($segment === '*') {
				if ($target instanceof \Phalcon\Support\Collection) {
					$target = $target->all();
				} elseif (! is_array($target)) {
					return value($default);
				}
				$result = array_pluck($target, $key);
				return in_array('*', $key) ? array_collapse($result) : $result;
			}
			if (array_accessible($target) && array_exists($target, $segment)) {
				$target = $target[$segment];
			} elseif (is_object($target) && isset($target->{$segment})) {
				$target = $target->{$segment};
			} else {
				return value($default);
			}
		}
		return $target;
	}
}

if (! function_exists('data_set')) {
	/**
	 * Set an item on an array or object using dot notation.
	 *
	 * @param  mixed  $target
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @param  bool  $overwrite
	 * @return mixed
	 */
	function data_set(&$target, $key, $value, $overwrite = true)
	{
		$segments = is_array($key) ? $key : explode('.', $key);
		if (($segment = array_shift($segments)) === '*') {
			if (! array_accessible($target)) {
				$target = [];
			}
			if ($segments) {
				foreach ($target as &$inner) {
					data_set($inner, $segments, $value, $overwrite);
				}
			} elseif ($overwrite) {
				foreach ($target as &$inner) {
					$inner = $value;
				}
			}
		} elseif (array_accessible($target)) {
			if ($segments) {
				if (! array_exists($target, $segment)) {
					$target[$segment] = [];
				}
				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite || ! array_exists($target, $segment)) {
				$target[$segment] = $value;
			}
		} elseif (is_object($target)) {
			if ($segments) {
				if (! isset($target->{$segment})) {
					$target->{$segment} = [];
				}
				data_set($target->{$segment}, $segments, $value, $overwrite);
			} elseif ($overwrite || ! isset($target->{$segment})) {
				$target->{$segment} = $value;
			}
		} else {
			$target = [];
			if ($segments) {
				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite) {
				$target[$segment] = $value;
			}
		}
		return $target;
	}
}