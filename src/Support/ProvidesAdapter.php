<?php

namespace Phalcon\Support;

trait ProvidesAdapter
{

	protected $adapterBuilders = [];

	protected $factory;

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

		if  (!is_null($this->factory)){
			return $this->callFactoryBuilder($config);
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

}