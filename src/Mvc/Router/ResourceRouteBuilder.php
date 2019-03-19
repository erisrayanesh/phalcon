<?php

namespace Phalcon\Mvc\Router;

use Phalcon\Mvc\Router\NestedGroup as RouteGroup;

class ResourceRouteBuilder
{

	protected $options = [];
	protected $controller;
	protected $defaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

	public function __construct($controller, array $options = [])
	{
		$this->controller = $controller;
		$this->options = (array) $options;
	}

	public function get()
	{

		$options = array_only($this->options, ["only", "except"]);
		array_forget($this->options, ["only", "except"]);

		$group = new RouteGroup($this->options);

		foreach ($this->getResourceMethods($this->defaults, $options) as $m) {
			$this->{'addResource'.ucfirst($m)}($group, $this->controller, $options);
		}

		return $group;
	}

	protected function getResourceMethods($defaults, $options)
	{
		if (isset($options['only'])) {
			return array_intersect($defaults, (array) $options['only']);
		} elseif (isset($options['except'])) {
			return array_diff($defaults, (array) $options['except']);
		}

		return $defaults;
	}

	protected function addResourceIndex(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addGet('', "$controller::index")->setName('index');
	}

	protected function addResourceCreate(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addGet('/create', "$controller::create")->setName('create');
	}

	protected function addResourceStore(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addPost('', "$controller::store")->setName('store');
	}

	protected function addResourceShow(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addGet('/{id:\d+}', "$controller::show")->setName('show');
	}

	protected function addResourceEdit(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addGet('/{id:\d+}/edit', "$controller::edit")->setName('edit');
	}

	protected function addResourceUpdate(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addPost('/{id:\d+}', "$controller::update")->setName('update');
	}

	protected function addResourceDestroy(RouteGroup &$group, $controller, array $options = [])
	{
		$group->addPost('/delete', "$controller::destroy")->setName('delete');
	}


}