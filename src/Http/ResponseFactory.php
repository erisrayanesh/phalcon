<?php
/**
 * Created by PhpStorm.
 * User: eris2
 * Date: 2/25/19
 * Time: 2:46 PM
 */

namespace Phalcon\Http;


class ResponseFactory
{
	/**
	 * @param string $content
	 * @param int $code
	 * @param null $status
	 * @param array $headers
	 * @return HttpResponse
	 */
	public function make($content = '', $code = 200, $status = null, $headers = [])
	{
		return new HttpResponse($content, $code, $status, $headers);
	}

	/**
	 * Create a new "no content" response.
	 *
	 * @param  int  $status
	 * @param  array  $headers
	 * @return \Phalcon\Http\HttpResponse
	 */
	public function noContent($status = 204, $headers = [])
	{
		return $this->make('', $status, $headers);
	}

	/**
	 * Create a new response for a given view.
	 *
	 * @param  string  $view
	 * @param  array  $data
	 * @param  int  $status
	 * @param  array  $headers
	 * @return \Phalcon\Http\HttpResponse
	 */
	public function view($view, $data = [], $status = 200, $headers = [])
	{
		return $this->make(getView($view, $data), $status, $headers);
	}

	/**
	 * Create a new JSON response instance.
	 *
	 * @param  mixed  $data
	 * @param  int  $status
	 * @param  array  $headers
	 * @param  int  $options
	 * @return \Phalcon\Http\JsonResponse
	 */
	public function json($data = [], $status = 200, $headers = [], $options = 15)
	{
		return new JsonResponse($data, $status, $headers, $options);
	}

	/**
	 * Create a new file download response.
	 *
	 * @param  string  $file
	 * @param  string|null  $name
	 * @param  array  $headers
	 * @return \Phalcon\Http\FileResponse
	 */
	public function download($file, $name = null, array $headers = [])
	{
		return new FileResponse($file, 200, $headers, $name,true);
	}

	/**
	 * Return the raw contents of a binary file.
	 *
	 * @param  string  $file
	 * @param  array  $headers
	 * @return \Phalcon\Http\FileResponse
	 */
	public function file($file, array $headers = [])
	{
		return new FileResponse($file, 200, $headers);
	}

	/**
	 * Create a new redirect response to the given path.
	 *
	 * @param  string  $path
	 * @param  int  $code
	 * @param  array  $headers
	 * @param  bool|null  $externalRedirect
	 * @return \Phalcon\Http\RedirectResponse
	 */
	public function redirectTo($path, $code = 302, $headers = [], $externalRedirect = false)
	{
		return new RedirectResponse($path, $code, $headers, $externalRedirect);
	}

	/**
	 * Create a new redirect response to a named route.
	 *
	 * @param  string  $route
	 * @param  array  $parameters
	 * @param  array  $query
	 * @param  int  $code
	 * @param  array  $headers
	 * @return \Phalcon\Http\RedirectResponse
	 */
	public function redirectToRoute($route, $parameters = [], $query = [], $code = 302, $headers = [])
	{
		return new RedirectResponse(route($route, $parameters, $query), $code, $headers);
	}

	public function redirectBack($status = 302, $headers = [], $fallback = false)
	{
		return new RedirectResponse($this->generator->previous($fallback), $status, $headers);
	}

}