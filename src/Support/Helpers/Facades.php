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
function config()
{
	$args = func_get_args();
	$config = DI()->getConfig();

	if (empty($args)) {
		return $config;
	}

	return call_user_func_array([$config, 'path'], $args);

//	return DI()->getConfig()->path($name, $default);
//    return DI()->getConfig()->get($name, $default);
}

/**
 * @return Phalcon\Mvc\Dispatcher
 */
function dispatcher()
{
	return DI()->get("dispatcher");
}

/**
 * @param null $name
 * @param null $params
 * @return \Phalcon\Mvc\View
 */
function view($name = null, $params = null)
{
	if (!empty($params)){
		DI()->get("view")->setVars($params);
	}

	if (!empty($name)){
		DI()->get("view")->pick($name);
	}

	return DI()->get("view");
}

/**
 * @return \Phalcon\Mvc\Url
 */
function url()
{
	return DI()->get("url");
}

/**
 * @param mixed $data
 * @return mixed
 */
function logger($data = null)
{

	if (is_null($data)) {
		return DI()->get("logger");
	}

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
 * @return \Phalcon\Http\Response\Cookies
 */
function cookies()
{
	return DI()->get("cookies");
}

/**
 * @return \Phalcon\Auth\AccessManager
 */
function access()
{
	return DI()->get("access");
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
 * @return \Phalcon\Auth\Manager
 */
function auth()
{
	return DI()->get("auth");
}

/**
 * @return \Phalcon\Mvc\Router
 */
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
 * @param string $key
 * @return League\Flysystem\AdapterInterface
 */
function storage($key = null)
{
	return DI()->get('filesystem')->get($key);
}

/**
 * @return \Phalcon\FileSystem\FileSystem
 */
function files()
{
	return DI()->get('files');
}