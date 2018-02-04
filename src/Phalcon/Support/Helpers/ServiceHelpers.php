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

if (! function_exists('t')) {
	function t($key, array $placeholders = [])
	{
		return DI()->get("locale")->_($key, $placeholders);
	}
}

function old($key, $default = null)
{
	return inputs()->getOld($key, $default);
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