<?php

namespace Phalcon\Support;


class ErrorsBag implements \Iterator, \Countable
{
	protected $errors = [];

	public function __construct($errors = [])
	{
		$this->errors = $errors;
	}

	public function set($key, $value)
	{
		$this->errors[] = [$key, $value];
	}

	public function get($key, $defaultValue = null)
	{
		$values = [];
		foreach ($this->errors as $err){
			if ($err[0] == $key){
				$values[] = $err[1];
			}
		}

		return count($values)? $values : $defaultValue;
	}

	public function remove($key)
	{
		foreach ($this->errors as $k => $err){
			if ($err[0] == $key){
				unset($this->errors[$k]);
			}
		}
	}

	public function has($key)
	{
		foreach ($this->errors as $err){
			if ($err[0] == $key){
				return true;
			}
		}
		return false;
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __isset($key)
	{
		return $this->has($key);
	}

	public function count()
	{
		return $this->count($this->errors);
	}

	public function current()
	{
		return current($this->errors);
	}

	public function next()
	{
		next($this->errors);
	}

	public function key()
	{
		return key($this->errors);
	}

	public function valid()
	{
		return isset($this->errors[$this->key()]);
	}

	public function rewind()
	{
		rewind($this->errors);
	}


}