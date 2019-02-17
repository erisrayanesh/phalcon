<?php

if (! function_exists('DI')) {
	function DI()
	{
		return \Phalcon\Di::getDefault();
	}
}

if (! function_exists('access')) {
	/**
	 * @return \Phalcon\Auth\AccessManager
	 */
	function access()
	{
		return DI()->get("access");
	}
}

if (! function_exists('auth')) {
	/**
	 * @param string $guard A guard name
	 * @return \Phalcon\Auth\Manager
	 */
	function auth($guard = null)
	{
		if (is_null($guard)) {
			return DI()->get("auth");
		}
		return DI()->get("auth")->guard($guard);
	}
}

if (! function_exists('config')) {
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
}

if (! function_exists('cookies')) {
	/**
	 * @return \Phalcon\Http\Response\Cookies
	 */
	function cookies()
	{
		return DI()->get("cookies");
	}
}

if (! function_exists('dispatcher')) {
	/**
	 * @return Phalcon\Mvc\Dispatcher
	 */
	function dispatcher()
	{
		return DI()->get("dispatcher");
	}
}

if (! function_exists('escaper')) {
	/**
	 * @return \Phalcon\Escaper
	 */
	function escaper()
	{
		return DI()->get('escaper');
	}
}

if (! function_exists('files')) {
	/**
	 * @return \Phalcon\FileSystem\FileSystem
	 */
	function files()
	{
		return DI()->get('files');
	}
}

if (! function_exists('filter')) {
	/**
	 * @return \Phalcon\Filter
	 */
	function filter()
	{
		return DI()->get('filter');
	}
}

if (! function_exists('flashSession')) {
	/**
	 * @return \Phalcon\Flash\Session
	 */
	function flashSession()
	{
		return DI()->get("flashSession");
	}
}

if (! function_exists('inputs')) {
	/**
	 * @return \Phalcon\Flash\FlashInputs
	 */
	function inputs()
	{
		return DI()->get("inputs");
	}
}

if (! function_exists('locale')) {
	/**
	 * @return \Phalcon\Translate\Locale
	 */
	function locale()
	{
		return DI()->get("locale");
	}
}

if (! function_exists('logger')) {
	/**
	 * @param mixed $data
	 * @return \Phalcon\Logger\Manager
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

		return DI()->get("logger")->debug(strval($data));
	}
}

if (! function_exists('logs')) {
	/**
	 * Get a log channel instance.
	 *
	 * @param  string  $channel
	 * @return \Phalcon\Logger\Manager|\Phalcon\Logger\Multiple
	 */
	function logs($channel = null)
	{
		return $channel ? logger()->channel($channel) : logger();
	}
}

if (! function_exists('request')) {
	/**
	 * @return \Phalcon\Http\Request
	 */
	function request()
	{
		return DI()->get("request");
	}
}

if (! function_exists('response')) {
	/**
	 * @return \Phalcon\Http\Response
	 */
	function response()
	{
		return DI()->get("response");
	}
}

if (! function_exists('router')) {
	/**
	 * @return \Phalcon\Mvc\Router
	 */
	function router()
	{
		return DI()->get("router");
	}
}

if (! function_exists('security')) {
	/**
	 * @return Phalcon\Security
	 */
	function security()
	{
		return DI()->get("security");
	}
}

if (! function_exists('session')) {
	/**
	 * @return Phalcon\Session\Adapter\Files
	 */
	function session()
	{
		return DI()->get("session");
	}
}

if (! function_exists('storage')) {
	/**
	 * @param string $key
	 * @return League\Flysystem\AdapterInterface
	 */
	function storage($key = null)
	{
		return DI()->get('filesystem')->get($key);
	}
}

if (! function_exists('url')) {
	/**
	 * @return \Phalcon\Mvc\Url
	 */
	function url()
	{
		return DI()->get("url");
	}
}

if (! function_exists('view')) {
	/**
	 * @param null $name
	 * @param null $params
	 * @return \Phalcon\Mvc\View
	 */
	function view($name = null, $params = null)
	{
		if (!empty($params)) {
			DI()->get("view")->setVars($params);
		}

		if (!empty($name)) {
			DI()->get("view")->pick($name);
		}

		return DI()->get("view");
	}
}