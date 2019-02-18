<?php

namespace Phalcon\Logger;

use Phalcon\Logger\Multiple as Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Adapter\RotatingFile;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Mvc\User\Component;

class Manager extends Component
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

	/**
	 * Returns an instance of Logger
	 * @param string|null $name Channel name
	 * @return Logger
	 * @throws \Exception
	 */
	public function channel(string $name = null)
	{
		$name = $name ?: $this->getDefaultChannel();

		if (isset($this->channels[$name]) && !is_array($this->channels[$name])){
			return $this->channels[$name];
		}

		return $this->channels[$name] = $this->resolveChannel($name);
	}

	public function setChannel(string $name, $config)
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

	public function setDefaultChannel(string $name)
	{
		$this->default = $name ?: $this->getDefaultChannel();
	}

	/**
	 * @param array $channels List of expected channels
	 * @return Logger
	 */
	public function stack(array $channels = [])
	{
		return $this->createStackDriver(compact('channels'));
	}

	/**
	 * Creates a LineFormatter instance
	 * @param $config
	 * @return LineFormatter
	 */
	public function createFileFormatter($config = null): LineFormatter
	{
		return new LineFormatter ($config['format'] ?? null , $config['date'] ?? null);
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

	/**
	 * Resolves the channel
	 * @param string $name
	 * @return Logger
	 * @throws \Exception
	 */
	protected function resolveChannel(string $name)
	{
		$config = $this->getChannelConfig($name);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Channel [{$name}] is not defined.");
		}

		if (isset($this->channelBuilders[$driver = $config['driver']])) {
			$logger = $this->createLogger($config);
			$this->channelBuilders[$driver]($name, $logger, $config);
			return $logger;
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
	protected function createSingleDriver($config): Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel');
		}

		$file = new FileAdapter ($config['file'], $config);
		$logger = $this->createLogger($config);
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
	protected function createRotatingDriver($config):Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel');
		}

		$file = new RotatingFile($config['file'], $config);
		$logger = $this->createLogger($config);
		$logger->push($file);
		return $logger;
	}

	protected function createStackDriver($config):Logger
	{
		if (!isset($config['channels'])){
			throw new \InvalidArgumentException('Channels list not defined for channel');
		}

		if (!array_accessible($config['channels'])){
			throw new \InvalidArgumentException('Channels attribute is not array for channel');
		}

		$logger = $this->createLogger($config);
		$adapters = collect($config['channels'])->flatMap(function ($channel) {
			return $this->channel($channel)->getLoggers();
		})->all();

		foreach ($adapters as $adapter) {
			$logger->push($adapter);
		}

		return $logger;
	}

	/**
	 * Creates a logger instance
	 * @param $config
	 * @return Logger
	 */
	protected function createLogger($config= null): Logger
	{
		$logger = new Logger();
		$logger->setLogLevel($config['level'] ?? \Phalcon\Logger::DEBUG);
		$logger->setFormatter($this->createFileFormatter($config));
		return $logger;
	}

}