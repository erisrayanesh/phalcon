<?php
namespace Phalcon\Flash;

use Phalcon\Di\Injectable;
use Phalcon\Support\Interfaces\Jsonable;

class FlashInputs extends Injectable implements \Countable, \ArrayAccess, \Iterator, Jsonable
{

	protected $inputsNewVar = "_inputs.new";
	protected $inputsOldVar = "_inputs.old";

	/**
	 * Returns an old flashed input value with given key
	 * @param $key
	 * @param $default
	 * @return mixed|null
	 */
	public function getOld($key = null, $default = null)
	{
		return array_get($this->allOld(), $key, $default);
	}

	/**
	 * Checks if old flashed inputs has a value with given key
	 * @param $key
	 * @return bool
	 */
	public function hasOld($key)
	{
		return $this->getOld($key) != null;
	}

	/**
	 * Returns an flashed input value with given key
	 * @param null $key
	 * @param null $default
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		return array_get($this->all(), $key, $default);
	}

	/**
	 * Flashes an input value with given key
	 * @param string|array $key
	 * @param bool $value
	 * @return $this
	 */
	public function set($key, $value = true)
	{
		if (!is_array($key)) {
			$key = [$key => $value];
		}

		$all = $this->all();

		foreach ($key as $k => $v) {
			$all[$k] = $v;
		}

		$this->session->set($this->inputsNewVar, $all);

		return $this;
	}

	/**
	 * Checks if flashed inputs has a value with given key
	 * @param $key
	 * @return bool
	 */
	public function has($key)
	{
		return $this->get($key) != null;
	}

	/**
	 * Alias of remove
	 * @param string|array $key
	 * @return FlashInputs
	 */
	public function forget($key)
	{
		return $this->remove($key);
	}

	/**
	 * Removes a flashed input with given key
	 * @param string|array $key
	 * @return $this
	 */
	public function remove($key)
	{
		array_forget($this->all(), $key);
		return $this;
	}

	public function clear()
	{
		$this->session->set($this->inputsNewVar, []);
		return $this;
	}

	public function flush()
	{
		return $this->clear();
	}

	public function allOld()
	{
		return $this->session->get($this->inputsOldVar, []);
	}

	public function all()
	{
		return $this->session->get($this->inputsNewVar, []);
	}

	public function save()
	{
		$this->session->set($this->inputsOldVar, $this->all());
		$this->session->set($this->inputsNewVar, []);
	}

	public function count()
	{
		return count($this->all());
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->all());
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		$this->remove($key);
	}

	/**
	 * Convert the flashed inputs to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();
	}

	public function current()
	{
		return current($this->all());
	}

	public function next()
	{
		next($this->all());
	}

	public function key()
	{
		return key($this->all());
	}

	public function valid()
	{
		return $this->has($this->key());
	}

	public function rewind()
	{
		rewind($this->all());
	}

	public function toJson($options = 0, $depth = 512)
	{
		return json_encode($this->all(), $options, $depth);
	}


}