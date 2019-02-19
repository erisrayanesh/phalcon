<?php

namespace Phalcon\Logger;

class Stack implements \Countable
{

	protected $adapters = [];

	public function __construct(array $adapters = [])
	{
		$this->setAdapters($adapters);
	}

	/**
	 * Adds an instance of adapterInterface to adapters stack
	 * @param AdapterInterface $adapter
	 * @return Stack
	 */
	public function addAdapter(AdapterInterface $adapter): Stack
	{
		$this->adapters[] = $adapter;
		return $this;
	}

	/**
	 * Sets the adapters stack
	 * @param array adapters An array of adapters
	 * @return Stack
	 * @throws \InvalidArgumentException
	 */
	public function setAdapters(array $adapters): Stack
	{
		foreach ($adapters as $key => $adapter) {
			if (!$adapter instanceof $adapter) {
				throw new \InvalidArgumentException("Adapter at index '$key' is not an instance of Phalcon\Logger\AdapterInterface");
			}
		}
		$this->adapters = $adapters;
		return $this;
	}

	public function getAdapters()
	{
		return $this->adapters;
	}

	public function __call($name, $arguments)
	{
		foreach ($this->adapters as $adapter){
			$adapter->{$name}($arguments);
		}
	}

	public function count()
	{
		return count($this->adapters);
	}

}