<?php

require_once "Helpers" . DIRECTORY_SEPARATOR . "ArrayHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "RequestHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "ResponseHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "ServiceHelpers.php";
require_once "Helpers" . DIRECTORY_SEPARATOR . "StringHelpers.php";

function validator($rules, $data = [])
{
	$validator = new \Phalcon\Validation();

	foreach ($rules as $rule) {
		$validator->add($rule[0], $rule[1]);
	}

	if (!empty($data)){
		$messages = $validator->validate($data);
		return $messages;
	}

	return $validator;
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

/**
 * @return \Phalcon\Security\Random
 */
function getSecurityRandom()
{
	return new \Phalcon\Security\Random();
}

/**
 * @return \Phalcon\Filter
 */
function getSanitizer()
{
	return new Phalcon\Filter();
}

function class_has_trait($object, $name)
{
	$traits = class_uses($object);
	return in_array($name, $traits);
}

/**
 * @param $controller
 * @param array $options
 * @return \Phalcon\Mvc\Router\GroupRecuresive
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
