<?php

namespace Phalcon\Support;

trait BuildsAdapterByFactory
{

	protected $factory;

	protected function callFactoryBuilder($config)
	{
		$factory = $this->factory;
		return $factory::load($config);
	}

}