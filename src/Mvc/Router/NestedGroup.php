<?php

namespace Phalcon\Mvc\Router;


class NestedGroup extends Group
{
	/**
	 * @var string
	 */
	protected $name = '';

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
			$paths = $this->mergePaths($this->getPaths(), $route->getPaths());
			$route->reConfigure($this->getPrefix() . $route->getPattern(), $paths);
			$route->setNamePrefix($this->getName());
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
		$mergedPaths = $this->mergePaths($this->getPaths(), $paths);
		$route = new NestedGroupRoute($this->getPrefix() . $pattern, $mergedPaths, $httpMethods);
		$route->setNamePrefix($this->getName())->setGroup($this);
		$this->_routes[] = $route;
		return $route;
	}

	protected function mergePaths($defaultPaths, $paths)
	{

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

		$mergedPaths = $this->mergePathParts($defaultPaths, $processedPaths);
		return $mergedPaths;
	}

	protected function mergePathParts(array $defaultPaths, array $paths)
	{
		$mergedParts = array_merge($defaultPaths, $paths);

		if (isset($defaultPaths['middleware']) && isset($paths['middleware'])){
			$defaultMiddleware = array_wrap(array_get($defaultPaths, 'middleware', []));
			$middleware = array_wrap(array_get($paths, 'middleware', []));
			$mergedParts['middleware'] = array_unique(array_merge($defaultMiddleware, $middleware));
		}

		return $mergedParts;
	}
}