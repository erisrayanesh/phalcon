<?php

namespace Phalcon\Support;

use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class Collection implements \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate, Arrayable, Jsonable
{

	protected $items = [];

	/**
	 * Collection constructor.
	 * @param array $items
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	public function has($key)
	{
		$keys = is_array($key) ? $key : func_get_args();
		foreach ($keys as $value) {
			if (! $this->offsetExists($value)) {
				return false;
			}
		}
		return true;
	}

	public function contains($key, $operator = null, $value = null)
	{
		if (func_num_args() == 1) {
			if ($this->useAsCallable($key)) {
				$placeholder = new stdClass;
				return $this->first($key, $placeholder) !== $placeholder;
			}
			return in_array($key, $this->items);
		}
		return $this->contains($this->operatorForWhere(...func_get_args()));
	}

	public function containsStrict($key, $value = null)
	{
		if (func_num_args() == 2) {
			return $this->contains(function ($item) use ($key, $value) {
				return data_get($item, $key) === $value;
			});
		}
		if ($this->useAsCallable($key)) {
			return ! is_null($this->first($key));
		}
		return in_array($key, $this->items, true);
	}

	public function prepend($value, $key = null)
	{
		$this->items = array_prepend($this->items, $value, $key);
		return $this;
	}

	public function push($value)
	{
		$this->offsetSet(null, $value);
		return $this;
	}

	public function pop()
	{
		return array_pop($this->items);
	}

	public function pull($key)
	{
		return array_pull($this->items, $key);
	}

	public function put($key, $value)
	{
		$this->offsetSet($key, $value);
		return $this;
	}

	public function get($key, $default = null)
	{
		if ($this->offsetExists($key)) {
			return $this->items[$key];
		}
		return value($default);
	}

	public function all()
	{
		return $this->items;
	}

	public function reverse()
	{
		return new static(array_reverse($this->items));
	}

	public function first(callable $callback = null, $default = null)
	{
		return array_first($this->items, $callback, $default);
	}

	public function last(callable $callback = null, $default = null)
	{
		return array_last($this->items, $callback, $default);
	}

	public function implode($key, $delimiter = null)
	{
		$first = $this->first();
		if (is_array($first) || is_object($first)) {
			return implode($delimiter, $this->pluck($key)->all());
		}
		return implode($key, $this->items);
	}

	public function pluck($value, $key = null)
	{
		return new static(array_pluck($this->items, $value, $key));
	}

	public function only($keys)
	{
		if (is_null($keys)) {
			return new static($this->items);
		}
		$keys = is_array($keys) ? $keys : func_get_args();
		return new static(array_only($this->items, $keys));
	}

	public function except($keys)
	{
		if ($keys instanceof self) {
			$keys = $keys->keys()->all();
		} elseif (! is_array($keys)) {
			$keys = func_get_args();
		}
		return new static(array_except($this->items, $keys));
	}

	public function reject($callback)
	{
		if ($this->useAsCallable($callback)) {
			return $this->filter(function ($value, $key) use ($callback) {
				return ! $callback($value, $key);
			});
		}
		return $this->filter(function ($item) use ($callback) {
			return $item != $callback;
		});
	}

	public function filter(callable $callback = null)
	{
		if ($callback) {
			return new static(array_where($this->items, $callback));
		}
		return new static(array_filter($this->items));
	}

	public function where($key, $operator, $value = null)
	{
		return $this->filter($this->operatorForWhere(...func_get_args()));
	}

	public function merge($items)
	{
		return new static(array_merge($this->items, $this->getArrayableItems($items)));
	}

	public function combine($values)
	{
		return new static(array_combine($this->all(), $this->getArrayableItems($values)));
	}

	public function isEmpty()
	{
		return empty($this->items);
	}

	public function isNotEmpty()
	{
		return ! $this->isEmpty();
	}

	public function keys()
	{
		return new static(array_keys($this->items));
	}

	public function forPage($page, $perPage)
	{
		$offset = max(0, ($page - 1) * $perPage);
		return $this->slice($offset, $perPage);
	}

	public function slice($offset, $length = null)
	{
		return new static(array_slice($this->items, $offset, $length, true));
	}

	public function shift()
	{
		return array_shift($this->items);
	}

	public function search($value, $strict = false)
	{
		if (! $this->useAsCallable($value)) {
			return array_search($value, $this->items, $strict);
		}
		foreach ($this->items as $key => $item) {
			if (call_user_func($value, $item, $key)) {
				return $key;
			}
		}
		return false;
	}

	public function map(callable $callback)
	{
		$keys = array_keys($this->items);

		$items = array_map($callback, $this->items, $keys);

		return new static(array_combine($keys, $items));
	}

	public function each(callable $callback)
	{
		foreach ($this->items as $key => $item) {
			if ($callback($item, $key) === false) {
				break;
			}
		}

		return $this;
	}

	function jsonSerialize()
	{
		return array_map(function ($value) {
			if ($value instanceof \JsonSerializable) {
				return $value->jsonSerialize();
			} elseif ($value instanceof \Jsonable) {
				return json_decode($value->toJson(), true);
			} elseif ($value instanceof Arrayable) {
				return $value->toArray();
			}
			return $value;
		}, $this->items);
	}

	public function toArray()
	{
		return array_map(function ($value) {
			return $this->getArrayable($value);
		}, $this->items);
	}

	public function toJson($options = 0)
	{
		return json_encode($this->jsonSerialize(), $options);
	}

	public function __set($key, $value)
	{
		$this->put($key, $value);
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
		return count($this->items);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->items);
	}
	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->items[$key];
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
		if (is_null($key)) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}
	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->items[$key]);
	}
	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();
	}


	protected function useAsCallable($value)
	{
		return ! is_string($value) && is_callable($value);
	}

	protected function operatorForWhere($key, $operator, $value = null)
	{
		if (func_num_args() == 2) {
			$value = $operator;
			$operator = '=';
		}
		return function ($item) use ($key, $operator, $value) {
			$retrieved = data_get($item, $key);
			$strings = array_filter([$retrieved, $value], function ($value) {
				return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
			});
			if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
				return in_array($operator, ['!=', '<>', '!==']);
			}
			switch ($operator) {
				default:
				case '=':
				case '==':  return $retrieved == $value;
				case '!=':
				case '<>':  return $retrieved != $value;
				case '<':   return $retrieved < $value;
				case '>':   return $retrieved > $value;
				case '<=':  return $retrieved <= $value;
				case '>=':  return $retrieved >= $value;
				case '===': return $retrieved === $value;
				case '!==': return $retrieved !== $value;
			}
		};
	}

	protected function getArrayable($value)
	{
		if (is_array($value)) {
			return $value;
		} elseif ($value instanceof self) {
			return $value->all();
		} elseif ($value instanceof \Arrayable) {
			return $value->toArray();
		} elseif ($value instanceof \Jsonable) {
			return json_decode($value->toJson(), true);
		} elseif ($value instanceof \JsonSerializable) {
			return $value->jsonSerialize();
		} elseif ($value instanceof \Traversable) {
			return iterator_to_array($value);
		}

		return $value;
	}

	protected function getArrayableItems($items)
	{
		return (array) $this->getArrayable($items);
	}

}