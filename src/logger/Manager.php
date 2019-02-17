<?php

namespace Phalcon\Logger;

use Phalcon\Logger\Multiple as Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Adapter\RotatingFile;
use Phalcon\Logger\Formatter\Line as LineFormatter;

class Manager
{

	protected $default;

	/**
	 * Loggers list
	 * @var array
	 */
	protected $channels = [];

	protected $channelBuilders = [];

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

		if (isset($this->channels[$name]) && !is_array($this->channels[$name])){
			return $this->channels[$name];
		}

		return $this->channels[$name] = $this->resolveChannel($name);
	}

	public function setChannel($name, $config)
	{
		if (! isset($config['driver'])){
			throw new \Exception('Driver is not set');
		}
		$this->channels[$name] = $config;
	}

	public function getDefaultChannel()
	{
		return $this->default;
	}

	public function setDefaultChannel($name)
	{
		$this->default = $name ?: $this->getDefaultChannel();
	}

	public function resolveChannel($name)
	{
		$config = $this->getChannelConfig($name);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Channel [{$name}] is not defined.");
		}

		if (isset($this->channelBuilders[$driver = $config['driver']])) {
			return $this->channelBuilders[$driver]($name, $config);
		}

		switch ($driver) {
			case 'single':
				return $this->createSingleDriver($name, $config);
			case 'rotating':
				return $this->createRotatingDriver($name, $config);
			default:
				throw new \InvalidArgumentException("Log driver {$driver} for channel {$name} is not defined.");
		}
	}

	/**
	 * Adds a custom channel builder
	 * @param string $channel Channel name
	 * @param \Closure $callback
	 * @return Manager
	 */
	public function addChannelBuilder(string $channel, \Closure $callback):Manager
	{
		$this->channelBuilders[$channel] = $callback->bindTo($this, $this);
		return $this;
	}

	protected function getChannelConfig(string $name)
	{
		if (!isset($this->channels[$name])){
			return null;
		}

		if (!is_array($this->channels[$name])){
			return null;
		}

		return $this->channels[$name];
	}

	/**
	 * Creates a Logger instance including a File adapter
	 * @param $name
	 * @param $config
	 * @return Logger
	 */
	protected function createSingleDriver($name, $config): Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel ' . $name);
		}

		$file = new FileAdapter ($config['file'], $config);
		$logger = new Logger();
		$logger->setLogLevel($config['level'] ?? \Phalcon\Logger::DEBUG);
		$logger->setFormatter($this->createFileFormatter($config));
		$logger->push($file);
		return $logger;
	}

	/**
	 * Creates a Logger instance including a RotatingFile adapter
	 * @param $name
	 * @param $config
	 * @return Logger
	 * @throws \Exception
	 */
	protected function createRotatingDriver($name, $config):Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel ' . $name);
		}

		$file = new RotatingFile($config['file'], $config);
		$logger = $this->createLogger($config);
		$logger->push($file);
		return $logger;
	}

	/**
	 * Creates a logger instance
	 * @param $config
	 * @return Logger
	 */
	protected function createLogger($config): Logger
	{
		$logger = new Logger();
		$logger->setLogLevel($config['level'] ?? \Phalcon\Logger::DEBUG);
		$logger->setFormatter($this->createFileFormatter($config));
		return $logger;
	}

	/**
	 * Creates a LineFormatter instance
	 * @param $config
	 * @return LineFormatter
	 */
	protected function createFileFormatter($config): LineFormatter
	{
		return new LineFormatter ($config['format'] ?? null , $config['date'] ?? null);
	}

}