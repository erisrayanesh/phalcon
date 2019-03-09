<?php

namespace Phalcon\FileSystem;

use Phalcon\Di\Injectable;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem as BaseFileSystem;

class Manager extends Injectable
{

	protected $default;
	protected $cloud;
	protected $adapters = [];


	/**
	 * @return array
	 */
	public function getAdapters()
	{
		return $this->adapters;
	}

	public function setAdapters(array $adapters)
	{
		$this->adapters = $adapters;
		return $this;
	}

	/**
	 * @param string $key
	 * @param $adapter
	 * @return FileSystemManager
	 */
	public function addAdapter($key, $adapter)
	{
		$this->adapters[$key] = $adapter;
		return $this;
	}

	public function getAdapter($key = null)
	{
		if (!empty($key) && isset($this->adapters[$key])){
			return $this->adapters[$key];
		}

		return null;
	}

	public function resolveAdapter($key)
	{
		$adapter = $this->getAdapter($key);

		if ($adapter instanceof \Closure){
			$this->adapters[$key] = $adapter;
		}

		return $adapter;
	}

	/**
	 * @param null $key
	 * @return AdapterInterface|null
	 */
	public function get($key = null)
	{
		return $this->resolveAdapter($key);
	}

	/**
	 * @param null $key
	 * @return AdapterInterface|null
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	public function __set($key, $adapter)
	{
		$this->adapters[$key] = $adapter;
	}

	public function __call($name, $arguments)
	{
		$default = $this->get($this->getDefault());

		if (empty($default)){
			return null;
		}

		if (method_exists($default, $name)){
			return call_user_func_array([$default, $name], $arguments);
		}

		throw new \RuntimeException("Method $name does not exist in {$this->getDefault()} file system driver");
	}

	/**
	 * @return string
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * @param mixed $default
	 * @return FileSystemManager
	 */
	public function setDefault($default)
	{
		$this->default = $default;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCloud()
	{
		return $this->cloud;
	}

	/**
	 * @param mixed $cloud
	 * @return FileSystemManager
	 */
	public function setCloud($cloud)
	{
		$this->cloud = $cloud;
		return $this;
	}





}