<?php

namespace Phalcon\Mvc\Router;


class GroupRecuresive extends Group
{

	/**
	 * @var string
	 */
	protected $name = '';

	public function __construct($paths = null)
	{
		if (is_array($paths)){
			$this->setName(array_pull($paths, "name", ''));
			$this->setPrefix(array_pull($paths, "prefix", ''));
		}
		parent::__construct($paths);
	}

	public function addGroup(GroupRecuresive $group)
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

//			$route instanceof Route;
			$route->reConfigure($this->getPrefix() . $route->getPattern(), $paths);
			$route->setName($this->getName().$group->getName().$route->getName());
			$this->_routes[] = $route;
		}
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

}