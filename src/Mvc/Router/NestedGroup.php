<?php

namespace Phalcon\Mvc\Router;


class NestedGroup extends Group
{
	/**
	 * @var string
	 */
	protected $name = '';

	protected $middleware = [];

	public static function make($paths = null, $routes = null)
	{
		$group = new static($paths);

		if (!is_null($routes)){
			$group->loadRoutes($routes);
		}

		return $group;
	}

	public function __construct($paths = null)
	{
		if (is_array($paths)) {
			$this->setMiddleware(array_pull($paths, "middleware", []));
			$this->setPrefix(array_pull($paths, "prefix", ''));
			$this->setName(array_pull($paths, "name", ''));
		}

		parent::__construct($paths);
	}

	/**
	 * Creates a group and then adds it to its collection and returns it
	 * @param null $paths
	 * @param null $routes
	 * @return NestedGroup
	 */
	public function group($paths = null, $routes = null)
	{
		return $this->addGroup(self::make($paths, $routes));
	}

	/**
	 * Adds a group routes to current instance routes list
	 * @param NestedGroup $group
	 * @return NestedGroup
	 */
	public function addGroup(NestedGroup $group)
	{
		foreach ($group->getRoutes() as $route){
			$paths = $this->mergePaths($route->getPaths());
			$route->reConfigure($this->getPrefix() . $route->getPattern(), $paths);
			$this->mergePathParts($route);
			$this->_routes[] = $route;
		}
		return $this;
	}

	/**
	 * Returns the prefix name of child routes
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return NestedGroup
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * @param $middleware
	 * @return NestedGroup
	 */
	public function setMiddleware($middleware)
	{
		$this->middleware = array_wrap($middleware);
		return $this;
	}

	public function loadRoutes($routes)
	{
		if ($routes instanceof \Closure) {
			$routes($this);
		} else {
			$router = $this;
			require $routes;
		}
	}

	protected function _addRoute($pattern, $paths = null, $httpMethods = null)
	{
		$mergedPaths = $this->mergePaths($paths);
		$route = new NestedGroupRoute($this->getPrefix() . $pattern, $mergedPaths, $httpMethods);
		$this->mergePathParts($route, false);
		$this->_routes[] = $route;
		return $route;
	}

	protected function mergePaths($paths)
	{

		$defaultPaths = $this->getPaths();

		if (!is_array($defaultPaths)) {
			return $paths;
		}

		$processedPaths = $paths;

		if (is_string($processedPaths)) {
			$processedPaths = Route::getRoutePaths($processedPaths);
		}

		if (!is_array($processedPaths)){
			return $defaultPaths;
		}

		$mergedPaths = array_merge($defaultPaths, $processedPaths);
		return $mergedPaths;
	}

//	protected function mergePathParts(array $defaultPaths, array $paths)
	protected function mergePathParts(NestedGroupRoute $route, $setGroup = false)
	{
//		$mergedParts = $defaultPaths, $paths);

//		if (isset($defaultPaths['middleware']) && isset($paths['middleware'])){
//			$defaultMiddleware = array_wrap(array_get($defaultPaths, 'middleware', []));
//			$middleware = array_wrap(array_get($paths, 'middleware', []));
//			$mergedParts['middleware'] = array_unique(array_merge($defaultMiddleware, $middleware));
//		}

//		return $mergedParts;

		$route->setNamePrefix($this->getName());
		$route->appendMiddleware($this->getMiddleware());

		if ($setGroup){
			$route->setGroup($this);
		}

		return $route;
	}
}