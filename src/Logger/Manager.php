<?php

namespace Phalcon\Logger;

use Phalcon\Logger\Stack as Logger;
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
	 * Dynamically call the default Adapter instance.
	 * @param $method
	 * @param $parameters
	 * @return mixed
	 * @throws \Exception
	 */

	public function __call($method, $parameters)
	{
		return $this->channel()->{$method}(...$parameters);
	}

	/**
	 * Returns an instance of Logger
	 * @param string|null $name Channel name
	 * @return Stack
	 * @throws \Exception
	 */
	public function channel(string $name = null)
	{
		$name = $name ?: $this->getDefaultChannel();

		if (isset($this->channels[$name]) && $this->channels[$name] instanceof Logger){
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
	 * @return Stack
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
	public function createLineFormatter($config = null): LineFormatter
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
	 * @return Stack
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
			$this->channelBuilders[$driver]($logger, $config);
			return $logger;
		}

		switch ($driver) {
			case 'single':
				return $this->createSingleDriver($config);
			case 'rotating':
				return $this->createRotatingDriver($config);
			default:
				throw new \InvalidArgumentException("Log driver {$driver} for channel {$name} is not defined.");
		}
	}

	protected function getChannelConfig(string $name)
	{
		if (!isset($this->channels[$name])){
			return null;
		}

		if ($this->channels[$name] instanceof Logger){
			return null;
		}

		return $this->channels[$name];
	}

	protected function resolveLogLevel($config): int
	{
		return intval($config['level'] ?? \Phalcon\Logger::DEBUG) ;
	}

	// Factory methods

	/**
	 * Creates a Logger instance including a File Adapter
	 * @param $name
	 * @param $config
	 * @return Stack
	 */
	protected function createSingleDriver($config): Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel');
		}

		$adapter = new FileAdapter ($config['file'], $config);
		$adapter->setLogLevel($this->resolveLogLevel($config));
		$adapter->setFormatter($this->createLineFormatter($config));
		return new Logger([$adapter]);
	}

	/**
	 * Creates a Logger instance including a RotatingFile Adapter
	 * @param $name
	 * @param $config
	 * @return Stack
	 * @throws \Exception
	 */
	protected function createRotatingDriver($config):Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel');
		}

		$adapter = new RotatingFile($config['file'], $config);
		$adapter->setLogLevel($this->resolveLogLevel($config));
		$adapter->setFormatter($this->createLineFormatter($config));
		return new Logger([$adapter]);
	}

	protected function createStackDriver($config):Logger
	{
		if (!isset($config['channels'])){
			throw new \InvalidArgumentException('Channels list not defined for channel');
		}

		if (!array_accessible($config['channels'])){
			throw new \InvalidArgumentException('Channels attribute is not array for channel');
		}

		$adapters = collect($config['channels'])->flatMap(function ($channel) {
			return $this->channel($channel)->getAdapters();
		})->all();
		return new Logger($adapters);
	}



}