<?php

function DI()
{
	return \Phalcon\Di::getDefault();
}

/**
 * @param $name
 * @param null $default
 * @return \Phalcon\Config|mixed
 */
function config($name, $default = null)
{
	return DI()->getConfig()->path($name, $default);
//    return DI()->getConfig()->get($name, $default);
}

function view($name, $params)
{
	DI()->get("view")->setVars($params);
    DI()->get("view")->pick($name);
}

/**
 * @return \Phalcon\Mvc\Url
 */
function url()
{
	return DI()->get("url");
}

function logger($data)
{

	$data = value($data);

	if ($data instanceof \Phalcon\Support\Interfaces\Arrayable) {
		$data = $data->toArray();
	}

	if (is_array($data)) {
		$data = print_r($data, true);
	}

	return DI()->get("logger")->info(strval($data));
}

/**
 * @return \Phalcon\Translate\Locale
 */
function locale()
{
	return DI()->get("locale");
}

/**
 * @return \Phalcon\Flash\Session
 */
function flashSession()
{
	return DI()->get("flashSession");
}

/**
 * @return \Phalcon\Http\Request
 */
function request()
{
	return DI()->get("request");
}

/**
 * @return \Phalcon\Http\Response
 */
function response()
{
	return DI()->get("response");
}

/**
 * @return Phalcon\Session\Adapter\Files
 */
function session()
{
	return DI()->get("session");
}

/**
 * @return \Phalcon\Flash\FlashInputs
 */
function inputs()
{
	return DI()->get("inputs");
}

/**
 * @return \Phalcon\Auth\Auth
 */
function auth()
{
	return DI()->get("auth");
}

function router()
{
	return DI()->get("router");
}

/**
 * @return Phalcon\Security
 */
function security()
{
	return DI()->get("security");
}

/**
 * @return \Phalcon\Acl\Acl
 */
function acl()
{
	return DI()->get("acl");
}



function route($name, $data = null)
{
	if (is_null($data)){
		$data = [];
	}

	if (!is_array($data)){
		$data = [$data];
	}

	$data["for"] = $name;
	return url()->get($data);
}

function dd($var)
{
	array_map(function ($x) {
//		$string = (new \Phalcon\Debug\Dump(null, true))->variable($x);
//
//		echo (PHP_SAPI == 'cli' ? strip_tags($string) . PHP_EOL : $string);
		dump($x);
	}, func_get_args());

	die(1);
}

function validator($rules, $data = [], $sendErrors = true)
{
	$validator = new \Phalcon\Validation();

	foreach ($rules as $rule) {
		$validator->add($rule[0], $rule[1]);
	}

	if (!empty($data)){
		$messages = $validator->validate($data);
		if($sendErrors) {
		    inputs()->addErrors($messages);
        }
		return $messages;
	}

	return $validator;
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
	return DI()->get("security")->getTokenKey();
}

function csrf_token()
{
	return DI()->get("security")->getToken();
}

function flash_error($errorKey = null, $error = null)
{

//	if ($errorKey instanceof \Phalcon\Validation\Message\Group){
//		foreach ($err)
//	}

	if (!is_array($errorKey) && !is_null($error)){
		$errorKey = [$errorKey => $error];
	}

	if (is_null($errorKey)){
		$errorKey = [];
	}

	foreach ($errorKey as $key => $msg){
		if (method_exists(flashSession(), $key)){

			if (!is_array($msg)){
				$msg = [$msg];
			}

			foreach ($msg as $item){
				flashSession()->{$key}($item);
			}
		}
	}
}

function redirect($location, $withKey = null, $with = null)
{
	flash_error($withKey, $with);
	return response()->redirect($location);
}

function redirect_back($withKey = null, $with = null)
{
	return redirect(old("_url"), $withKey, $with);
}

function redirect_route($name, $data = null, $withKey = null, $with = null)
{
	return redirect(route($name, $data), $withKey, $with);
}

function old($key, $default = null)
{
	return inputs()->getOld($key, $default);
}

if (! function_exists('collect')) {
	/**
	 * Creates new collection from array
	 * @param $items
	 * @return \Phalcon\Support\Collection
	 */
	function collect($items)
	{
		return new \Phalcon\Support\Collection($items);
	}
}

if (! function_exists('t')) {
	function t($key, array $placeholders = [])
	{
		return DI()->get("locale")->_($key, $placeholders);
	}
}

if (! function_exists('camelize')) {
	function camelize($text, $delimiter = null)
	{
		return \Phalcon\Text::camelize($text, $delimiter);
	}
}

if (! function_exists('uncamelize')) {
	function uncamelize($text, $delimiter = null)
	{
		return \Phalcon\Text::uncamelize($text, $delimiter);
	}
}

if (! function_exists('toHtml')) {
	function toHtml ($value)
	{
		return html_entity_decode($value, ENT_QUOTES, "UTF-8");
	}
}

if (! function_exists('toSafeHtml')) {
	function toSafeHtml ($value)
	{
		return htmlentities($value, ENT_QUOTES, "UTF-8", false);
	}
}

if (! function_exists('numberUnformat')) {
	function numberCleanFormat($text, $thousands_sep = ",")
	{
		$text = strval($text);
		return str_replace($thousands_sep, "", $text);
	}
}

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

if (! function_exists('str_contains')) {
	function str_contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle) {
			if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}
}

if (! function_exists('str_limit')) {
	function str_limit($value, $limit = 100, $end = '...')
	{
		if (mb_strwidth($value, 'UTF-8') <= $limit) {
			return $value;
		}
		return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
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


//Array helpers

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


function class_has_trait($object, $name)
{
	$traits = class_uses($object);
	return in_array($name, $traits);
}

function request_only($list)
{

	if (!is_array($list)){
		$list = func_get_args();
	}

	if (!is_array($list)){
		$list = [];
	}

	$values = [];
	foreach ($list as $item){
		$values[$item] = request()->get($item);
	}
	return $values;
}

function request_except($list)
{

	if (!is_array($list)){
		$list = func_get_args();
	}

	if (!is_array($list)){
		$list = [];
	}

	$keys = array_keys(array_except($_REQUEST, $list));

	$values = [];
	foreach ($keys as $item){
		$values[$item] = request()->get($item);
	}
	return $values;
}

function request_expects_json()
{
	return (request()->isAjax() && ! request_is_pjax()) || request_wants_json();
}

function request_wants_json()
{
	$acceptable = request()->getAcceptableContent();

	return isset($acceptable[0]) && str_contains($acceptable[0], ['/json', '+json']);
}

function request_is_pjax()
{
	return request()->getHeader('X-PJAX') == true;
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
