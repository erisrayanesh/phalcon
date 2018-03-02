<?php

namespace Phalcon\FileSystem;

use Phalcon\Mvc\User\Component;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem as BaseFileSystem;

class FileSystemManager extends Component
{

	protected $default;
	protected $cloud;
	protected $adapters = [];

	public function __construct()
	{

	}

	/**
	 * @return array
	 */
	public function getAdapters()
	{
		return $this->adapters;
	}

	/**
	 * @param string $key
	 * @param $adapter
	 * @return FileSystemManager
	 */
	public function addAdapter($key, AdapterInterface $adapter)
	{
		$this->adapters[$key] = $adapter;
		return $this;
	}

	/**
	 * @param null $key
	 * @return AdapterInterface|null
	 */
	public function get($key = null)
	{
		if (!empty($key) && isset($this->adapters[$key])){
			$adapter = $this->adapters[$key];
		}

		if (isset($this->adapters[$this->getDefault()])){
			$adapter = $this->adapters[$this->getDefault()];
		}

		if (!empty($adapter)){
			return new BaseFileSystem($adapter);
		}

		return null;
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

		foreach ($this->getAdapters() as $adapter) {
			return call_user_func_array([$adapter, $name], $arguments);
		}
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