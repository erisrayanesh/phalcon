<?php

namespace Phalcon\Mvc\Router;

class NestedGroupRoute extends Route
{

	protected $middleware = [];

	public function __construct($pattern, $paths = null, $httpMethods = null)
	{
		$this->setMiddleware(array_pull($paths, 'middleware', []));
		parent::__construct($pattern, $paths, $httpMethods);
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
	public function setMiddleware(array $middleware)
	{
		$this->middleware = $middleware;
		return $this;
	}

}