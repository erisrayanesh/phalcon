<?php

namespace Phalcon\Support;

abstract class Manager extends \Phalcon\Di\Injectable
{

	protected $default;

	/**
	 * Drivers list
	 * @var array
	 */
	protected $drivers = [];

	protected $driverBuilders = [];

	protected $driverType = "";

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return $this->driver()->{$method}(...$parameters);
	}

	/**
	 * Returns a driver instance
	 * @param null $name
	 * @return mixed
	 */
	public function driver($name = null)
	{
		$name = $name ?: $this->getDefault();

		if (is_null($name)) {
			throw new \InvalidArgumentException(sprintf(
				'Unable to resolve NULL driver for [%s].', static::class
			));
		}

		if (isset($this->drivers[$name]) && is_a($this->drivers[$name], $this->driverType)){
			return $this->drivers[$name];
		}

		return $this->drivers[$name] = $this->createDriver($name);
	}

	public function setDriver($name, $driver)
	{
		$this->drivers[$name] = $driver;
	}

	public function getDefault()
	{
		return $this->default;
	}

	public function setDefault($name)
	{
		$this->default = $name ?: $this->getDefault();
	}

	public function addDriverBuilder($name, callable $builder)
	{
		$this->driverBuilders[$name] = $builder;
		return $this;
	}

	protected function createDriver($name)
	{
		$config = $this->getDriverConfig($name);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Driver [{$name}] is not defined.");
		}

		if (isset($this->driverBuilders[$adapter = $config['driver']])) {
			return $this->driverBuilders[$adapter]($name, $config);
		}

		$method = "create".camelize($adapter)."Adapter";

		if (method_exists($this, $method)){
			return $this->{$method}($name, $config);
		}

		throw new \InvalidArgumentException("Unknown or undefined adapter {$adapter} specified for driver {$name}.");
	}

	protected function getDriverConfig($name)
	{
		if (!isset($this->drivers[$name])){
			return null;
		}

		if (is_a($this->drivers[$name], $this->driverType)){
			return null;
		}

		return $this->drivers[$name];
	}

	protected function callCustomCreator($driver)
	{
		return $this->customCreators[$driver]($this->app);
	}
}