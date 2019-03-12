<?php

namespace Phalcon\Support;

trait BuildsAdapterByNamespace
{

	protected $namespace;

	protected function callInstanceBuilder($adapter, $config)
	{
		$class = trim($this->namespace, '\\\/') . "\\" . camelize($adapter);
		return new $class($config);
	}

}