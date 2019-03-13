<?php

namespace Phalcon\Support;

trait ProvidesAdapter
{
	protected $factory;

	protected $namespace;

	protected $adapterBuilders = [];

	public function addAdapterBuilder($name, callable $builder)
	{
		$this->adapterBuilders[$name] = $builder;
		return $this;
	}

	public function createAdapter($name, $config = [])
	{
		if (is_null($config)) {
			return null;
		}

		if ($this->hasCustomAdapterBuilder($name)) {
			return $this->callCustomAdapterBuilder($name, $config);
		}

		if (method_exists($this, $method = "create".camelize($name)."Adapter")){
			return $this->{$method}($name, $config);
		}

		if  (!empty($this->factory)){
			return $this->callFactoryBuilder($config);
		}

		if  (!empty($this->namespace)){
			return $this->callInstanceBuilder($name, $config);
		}

		throw new \InvalidArgumentException("Undefined adapter {$name}.");
	}

	public function hasCustomAdapterBuilder($name)
	{
		return isset($this->adapterBuilders[$name]);
	}

	protected function callCustomAdapterBuilder($driver, $config)
	{
		return $this->adapterBuilders[$driver]($config);
	}

	protected function callFactoryBuilder($config)
	{
		$factory = $this->factory;
		return $factory::load($config);
	}

	protected function callInstanceBuilder($adapter, $config)
	{
		$class = trim($this->namespace, '\\\/') . "\\" . camelize($adapter);
		return new $class($config);
	}

}