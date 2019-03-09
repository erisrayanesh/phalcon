<?php

if (! function_exists('DI')) {
	function DI()
	{
		return \Phalcon\Di::getDefault();
	}
}

if (! function_exists('access')) {
	/**
	 * @return \Phalcon\Auth\Manager
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

if (! function_exists('cookie')) {
	/**
	 * @param null $name
	 * @param null $value
	 * @param int $expire
	 * @param null $path
	 * @param bool $secure
	 * @param null $domain
	 * @param bool $httpOnly
	 * @return \Phalcon\Http\Cookie|\Phalcon\Http\Cookie\Factory
	 */
	function cookie($name = null, $value = null, $expire = 0, $path = null, $secure = false, $domain = null, $httpOnly = true)
	{
		$cookies = DI()->get('cookie');

		if (is_null($name)) {
			return $cookies;
		}

		return $cookies->make($name, $value, $expire, $path, $secure, $domain, $httpOnly);
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

if (! function_exists('flashInputs')) {
	/**
	 * @return \Phalcon\Flash\FlashInputs
	 */
	function flashInputs()
	{
		return DI()->get("flashInputs");
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
	function logger(...$data)
	{
		if (empty($data)) {
			return DI()->get("logger");
		}

		foreach ($data as $item) {
			$item = value($item);

			if ($item instanceof \Phalcon\Support\Interfaces\Arrayable) {
				$item = $item->toArray();
			}

			if (is_array($item)) {
				$item = print_r($item, true);
			}

			return DI()->get("logger")->debug(strval($item));
		}
	}
}

if (! function_exists('logs')) {
	/**
	 * Get a log channel instance.
	 *
	 * @param  string  $channel
	 * @param  mixed  $data
	 * @return \Phalcon\Logger\Manager|\Phalcon\Logger\Multiple
	 * @throws \Exception
	 */
	function logs($channel = null, ...$data)
	{
		$ch = $channel ? logger()->channel($channel) : logger();

		if (empty($data)) {
			return $ch;
		}

		foreach ($data as $item) {
			$item = value($item);

			if ($item instanceof \Phalcon\Support\Interfaces\Arrayable) {
				$item = $item->toArray();
			}

			if (is_array($item)) {
				$item = print_r($item, true);
			}

			return $ch->debug(strval($item));
		}
	}
}

if (! function_exists('request')) {
	/**
	 * @param null $key
	 * @param null $default
	 * @param null $filters
	 * @param bool $notAllowEmpty
	 * @param bool $noRecursive
	 * @return string|array|\Phalcon\Http\FormRequest
	 */
	function request($key = null, $default = null, $filters = null, $notAllowEmpty = false, $noRecursive = false)
	{
		if (is_null($key)) {
			return DI()->get("request");
		}

		if (is_array($key)) {
			return DI()->get("request")->only($key);
		}

		$value = DI()->get("request")->get($key, $filters, $default, $notAllowEmpty, $noRecursive);

		return is_null($value) ? value($default) : $value;
	}
}

if (! function_exists('response')) {
	/**
	 * @param string $content
	 * @param int $code
	 * @param null $status
	 * @param array $headers
	 * @return \Phalcon\Http\ResponseFactory
	 */
	function response($content = '', $code = 200, $status = null, $headers = [])
	{
		$factory = DI()->get("response");

		if (func_num_args() === 0) {
			return $factory;
		}

		return $factory->make($content, $code, $status, $headers);
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
	 * @param null $key
	 * @param null $default
	 * @return mixed|Phalcon\Session\Adapter\Files
	 */
	function session($key = null, $default = null)
	{
		if (is_null($key)) {
			return DI()->get("session");
		}

		if (is_array($key)) {
			return DI()->get("session")->set($key);
		}

		return DI()->get("session")->get($key, $default);
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
	 * @param null $path
	 * @param array $parameters
	 * @return \Phalcon\Mvc\Url|string
	 */
	function url($path = null, $parameters = [])
	{
		if (is_null($path)) {
			return DI()->get("url");
		}

		return DI()->get("url")->get($path, $parameters);
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