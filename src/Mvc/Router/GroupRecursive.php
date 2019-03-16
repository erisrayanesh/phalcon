<?php

namespace Phalcon\Mvc\Router;


class GroupRecursive extends Group
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
		if (is_array($paths)){
			$this->setName(array_pull($paths, "name", ''));
			$this->setPrefix(array_pull($paths, "prefix", ''));
		}
		parent::__construct($paths);
	}

	/**
	 * Creates a group and then adds it to its collection and returns it
	 * @param null $paths
	 * @param null $routes
	 * @return GroupRecursive
	 */
	public function group($paths = null, $routes = null)
	{
		$group = self::make($paths, $routes);
		$this->addGroup($group);
		return $group;
	}

	/**
	 * @param GroupRecursive $group
	 * @return GroupRecursive
	 */
	public function addGroup(GroupRecursive $group)
	{
		$module = $this->getModule();
		$namespace = $this->getNamespace();
		foreach ($group->getRoutes() as $route){
			$paths = $route->getPaths();

			if ($module != null && array_get($paths, 'module') == null){
				$paths['module'] = $module;
			}

			if ($namespace != null && array_get($paths, 'namespace') == null){
				$paths['namespace'] = $namespace;
			}

			$route->reConfigure($this->getPrefix() . $route->getPattern(), $paths);
			$this->_routes[] = $route;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getModule()
	{
		return array_get($this->getPaths(), 'module', null);
	}

	public function getNamespace()
	{
		return array_get($this->getPaths(), 'namespace', null);
	}

	public function getRoutes()
	{
		$routes = parent::getRoutes();
		foreach ($routes as $route) {
			$route->setName($this->getName().$route->getName());
		}
		return $routes;
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


}