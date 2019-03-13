<?php

require_once "Helpers" . DIRECTORY_SEPARATOR . "ArrayHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "RequestHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "ResponseHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "Facades.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "StringHelpers.php";

if (! function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof \Closure ? $value() : $value;
	}
}

if (! function_exists('validator')) {
	/**
	 * @param $rules
	 * @param array $data
	 * @return \Phalcon\Validation
	 */
	function validator($rules, $data = [])
	{
		$validator = new \Phalcon\Validation();

		foreach ($rules as $rule) {
			$validator->add($rule[0], $rule[1]);
		}

		if (!empty($data)) {
			$validator->validate($data);
		}

		return $validator;
	}
}

if (! function_exists('collect')) {
	/**
	 * Creates new collection from array
	 * @param $items
	 * @return \Phalcon\Support\Collection
	 */
	function collect($items)
	{

		if ($items instanceof \Phalcon\Mvc\Model\Resultset){
			$array = [];
			foreach ($items as $item){
				$array[] = $item;
			}
			$items = $array;
		}

		return new \Phalcon\Support\Collection($items);
	}
}

if (! function_exists('has_trait')) {
	function has_trait($object, $name)
	{
		$traits = class_uses_recursive($object);
		return in_array($name, $traits);
	}
}

if (! function_exists('class_uses_recursive')) {
	/**
	 * Returns all traits used by a class, its subclasses and trait of their traits.
	 *
	 * @param  object|string  $class
	 * @return array
	 */
	function class_uses_recursive($class)
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		$results = [];

		foreach (array_merge([$class => $class], class_parents($class)) as $class) {
			$results += trait_uses_recursive($class);
		}

		return array_unique($results);
	}
}

if (! function_exists('trait_uses_recursive')) {
	/**
	 * Returns all traits used by a trait and its traits.
	 *
	 * @param  string  $trait
	 * @return array
	 */
	function trait_uses_recursive($trait)
	{
		$traits = class_uses($trait);

		foreach ($traits as $trait) {
			$traits += trait_uses_recursive($trait);
		}

		return $traits;
	}
}

if (! function_exists('class_basename')) {
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}

if (! function_exists('windows_os')) {
	function windows_os()
	{
		return strtolower(substr(PHP_OS, 0, 3)) === 'win';
	}
}

if (! function_exists('tap')) {
	/**
	 * Call the given Closure with the given value then return the value.
	 *
	 * @param  mixed  $value
	 * @param  callable|null  $callback
	 * @return mixed
	 */
	function tap($value, $callback = null)
	{
		if (is_null($callback)) {
			return $value;
		}

		$callback($value);

		return $value;
	}
}

/**
 * @return \Phalcon\Security\Random
 */
function getSecurityRandom()
{
	return new \Phalcon\Security\Random();
}

/**
 * @param $controller
 * @param array $options
 * @return \Phalcon\Mvc\Router\GroupRecursive
 */
function resourceRoute($controller, array $options = [])
{
	$res = new \Phalcon\Mvc\Router\ResourceRouteBuilder($controller, $options);
	return $res->get();
}

function getUserLocale()
{

	$client = request()->getServer('HTTP_CLIENT_IP');
	$forward = request()->getServer('HTTP_X_FORWARDED_FOR');
	$remote = request()->getServer('REMOTE_ADDR');

	if (filter_var($client, FILTER_VALIDATE_IP))
		$ip = $client;
	elseif (filter_var($forward, FILTER_VALIDATE_IP))
		$ip = $forward;
	else
		$ip = $remote;

	return @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
}

function can($ability, $arguments = [])
{
	return auth()->guard()->check($ability, $arguments);
}

function cant($ability, $arguments = [])
{
	return !auth()->guard()->check($ability, $arguments);
}

function cannot($ability, $arguments = [])
{
	return cant($ability, $arguments);
}

if (! function_exists('t')) {
	function t($key, array $placeholders = [], $local = null)
	{
		return translate()->get($key, $placeholders, $local);
	}
}

function old($key, $default = null)
{
	return flashInputs()->getOld($key, $default);
}

function csrf_field()
{
	return Phalcon\Tag::hiddenField([
		'id' => csrf_key(),
		'name'  => csrf_key(),
		'value' => csrf_token()
	]);
}

function csrf_key()
{
	return security()->getTokenKey();
}

function csrf_token()
{
	return security()->getToken();
}


