<?php

namespace Phalcon\Mvc;


use Phalcon\Mvc\Model\Traits\HasEagerLoading;
use Phalcon\Mvc\Model\Traits\HasAttributes;
use Phalcon\Mvc\Model\Traits\HasTimestamps;
use Phalcon\Mvc\Model\Traits\HasTracker;
use Phalcon\Mvc\Model\Traits\InteractsWithPivotTable;

abstract class AbstractModel extends Model
{

	use HasTimestamps,
		HasTracker,
		HasAttributes,
		InteractsWithPivotTable,
		HasEagerLoading;

	protected $guarded = [];

	protected $timestamps = true;
	protected $tracker = true;

	public function initializing()
	{

	}

	public function initialized()
	{

	}

	public function initialize()
	{
		$this->initializing();

		$class = static::class;
		foreach (class_uses_recursive($class) as $trait) {
			$method = 'init' . class_basename($trait);
			if (method_exists($this, $method)) {
				call_user_func([$this, $method]);
			}
		}

		$this->initialized();
	}

	public function toArray($columns = null)
	{
		$arr = parent::toArray($columns);

		array_forget($arr, $this->getGuarded());

		if (method_exists($this, 'getAllAppendedAttributeValues')){
			$arr = array_merge($arr, $this->getAllAppendedAttributeValues());
		}

		$arr = array_merge($arr, $this->getRelationsToArray());

		if (isset($this->pivot)) {
			$value = $this->pivot;

			if ($value instanceof Model){
				$value = $value->toArray();
			}

			$arr["pivot"] = $value;
		};

		return $arr;
	}

	public static function whereIn($arr)
	{
		return implode(',', array_map(function($value){
			return is_string($value)? "'$value'" : $value;
		}, $arr));
	}

	/**
	 * @return array
	 */
	public function getGuarded()
	{
		return $this->guarded;
	}

	/**
	 * @param array $guarded
	 * @return AbstractModel
	 */
	public function setGuarded(array $guarded)
	{
		$this->guarded = $guarded;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasTimestamps()
	{
		return $this->timestamps;
	}

	/**
	 * @param bool $timestamps
	 * @return AbstractModel
	 */
	public function setTimestamps($timestamps)
	{
		$this->timestamps = (bool) $timestamps;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasTracker()
	{
		return $this->tracker;
	}

	/**
	 * @param bool $tracker
	 * @return AbstractModel
	 */
	public function setTracker($tracker)
	{
		$this->tracker = (bool) $tracker;
		return $this;
	}


	protected function getRelationsToArray()
	{

		if (!is_array($this->_related)){
			return [];
		}

		$results = [];
		foreach ($this->_related as $key => $related) {
			if (is_array($related) || $related instanceof Simple) {
				foreach ($related as $model) {
					$results[$key][] = $model->toArray();
				}
			}
			if ($related instanceof Model) {
				$results[$key] = $related->toArray();
			}
		}
		return $results;

	}



}