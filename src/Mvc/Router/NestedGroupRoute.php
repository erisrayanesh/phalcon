<?php

namespace Phalcon\Mvc\Router;

class NestedGroupRoute extends Route
{

	protected $middleware = [];

	public function reConfigure($pattern, $paths = null)
	{
		if ($middleware = array_pull($paths, 'middleware')){
			$this->setMiddleware($middleware);
		}

		parent::reConfigure($pattern, $paths);
	}

	public function setNamePrefix($name)
	{
		$this->_name = $name . $this->_name;
		return $this;
	}

	/**
	 * @param mixed $name
	 * @return NestedGroupRoute
	 */
	public function setName($name)
	{
		$this->_name = $this->_name . $name;
		return  $this;
	}

	/**
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * @param array $middleware
	 * @return NestedGroupRoute
	 */
	public function setMiddleware($middleware)
	{
		$this->middleware = array_wrap($middleware);
		return $this;
	}

	public function appendMiddleware($middleware)
	{

		if (empty($middleware)) {
			return $this;
		}

		if (is_string($middleware)) {
			$middleware = func_get_args();
		}

		$middleware = array_wrap($middleware);

		$this->middleware = array_merge($middleware, $this->middleware);
		return $this;
	}

}