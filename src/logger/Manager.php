<?php

namespace Phalcon\Logger;

class Manager
{

	protected $default;

	/**
	 * Loggers list
	 * @var array
	 */
	protected $channels = [];

	/**
	 * Dynamically call the default adapter instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return $this->channel()->{$method}(...$parameters);
	}

	public function channel($name = null)
	{
		$name = $name ?: $this->getDefaultChannel();

		if (isset($this->loggers[$name]) && !is_array($this->loggers[$name])){
			return $this->loggers[$name];
		}

		return $this->loggers[$name] = $this->resolveLogger($name);
	}

	public function setChannel($name, $driver, array $provider)
	{
		$this->loggers[$name] = [
			"driver" => $driver,
			"provider" => $provider,
		];
	}

	public function getDefaultChannel()
	{
		return $this->default;
	}

	public function setDefaultChannel($name)
	{
		$this->default = $name ?: $this->getDefaultChannel();
	}



}