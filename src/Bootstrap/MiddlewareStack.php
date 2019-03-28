<?php

namespace Phalcon\Bootstrap;

use Phalcon\Di\Injectable;

class MiddlewareStack extends Injectable
{
	/**
	 * The object being passed through the middleware.
	 * @var mixed
	 */
	protected $passable;

	protected $method = "handle";

	/**
	 * The array of middlewares.
	 *
	 * @var array
	 */
	protected $middleware = [];

	public function __construct()
	{
	}

	/**
	 * Set the object being passed through the middleware.
	 *
	 * @param  mixed  $passable
	 * @return $this
	 */
	public function setPassable($passable)
	{
		$this->passable = $passable;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param string $method
	 * @return MiddlewareStack
	 */
	public function setMethod(string $method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Set the array of middlewares.
	 *
	 * @param  array|mixed  $middleware
	 * @return $this
	 */
	public function setStack($middleware)
	{
		$this->middleware = is_array($middleware) ? $middleware : func_get_args();
		return $this;
	}

	/**
	 * Run the pipeline with a final destination callback.
	 *
	 * @param  \Closure  $core
	 * @return mixed
	 */
	public function run(\Closure $core)
	{
		$director = array_reduce(array_reverse($this->middleware), $this->reduce(), $this->prepareCore($core));
		return $director($this->passable);
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 *
	 * @return \Closure
	 */
	protected function reduce()
	{
		return function ($stack, $middleware) {
			return function ($passable) use ($stack, $middleware) {

				if (is_callable($middleware)) {
					return $middleware($passable, $stack);
				}

				if (! is_object($middleware)) {
					[$name, $parameters] = $this->parseStringMiddleware($middleware);
					$middleware = $this->getDI()->get($name);
					$parameters = array_merge([$passable, $stack], $parameters);
				} else {
					$parameters = [$passable, $stack];
				}

				$response = method_exists($middleware, $this->method)
					? $middleware->{$this->getMethod()}(...$parameters)
					: $middleware(...$parameters);

				return $response;
			};
		};
	}

	protected function prepareCore(\Closure $core)
	{
		return function ($passable) use ($core) {
			return $core($passable);
		};
	}

	protected function parseStringMiddleware($middleware)
	{
		[$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);

		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}

		return [$name, $parameters];
	}
}