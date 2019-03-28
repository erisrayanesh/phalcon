<?php

namespace Phalcon\Mvc\Router;

class NestedGroupRoute extends Route
{

	/**
	 * @param mixed $name
	 * @return NestedGroupRoute
	 */
	public function setName($name)
	{
		$this->_name = isset($this->_paths['name_prefix']) ? $this->_paths['name_prefix'].$name : $name;
		return $this;
	}

}