<?php

namespace Phalcon\Mvc\Router;

class NestedGroupRoute extends Route
{

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



}