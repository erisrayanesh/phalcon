<?php

namespace Phalcon\Logger;

use Phalcon\Support\BuildsAdapterByFactory;
use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Logger\Stack as Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Adapter\RotatingFile;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Support\ProvidesAdapter;

class Manager extends BaseManager
{

	use ProvidesAdapter, BuildsAdapterByFactory;

	protected $driverType = Logger::class;

	protected $factory = Factory::class;

	/**
	 * @param array $channels List of expected channels
	 * @return Stack
	 */
	public function stack(array $channels = [])
	{
		return $this->createStackAdapter(compact('channels'));
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

	protected function resolveLogLevel($config): int
	{
		return intval($config['level'] ?? \Phalcon\Logger::DEBUG) ;
	}

	protected function createDriver($name)
	{
		$adapter = parent::createDriver($name);

		if (! $adapter instanceof Logger){
			$adapter = new Logger([$adapter]);
		}

		return $adapter;
	}

	// Factory methods

	/**
	 * Creates a Logger instance including a File Adapter
	 * @param $name
	 * @param $config
	 * @return Stack
	 */
	protected function createSingleAdapter($config): Logger
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
	protected function createRotatingAdapter($config):Logger
	{
		if (!isset($config['file'])){
			throw new \InvalidArgumentException('File path not defined for channel');
		}

		$adapter = new RotatingFile($config['file'], $config);
		$adapter->setLogLevel($this->resolveLogLevel($config));
		$adapter->setFormatter($this->createLineFormatter($config));
		return new Logger([$adapter]);
	}

	protected function createStackAdapter($config):Logger
	{
		if (!isset($config['channels'])){
			throw new \InvalidArgumentException('Channels list not defined for channel');
		}

		if (!array_accessible($config['channels'])){
			throw new \InvalidArgumentException('Channels attribute is not array for channel');
		}

		$adapters = collect($config['channels'])->flatMap(function ($channel) {
			return $this->driver($channel)->getAdapters();
		})->all();
		return new Logger($adapters);
	}

}