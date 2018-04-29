<?php

namespace Phalcon\Mvc;


use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\Resultset\Advanced;
use Phalcon\Mvc\Model\Resultset\Simple;
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

	public function getResultsetClass()
	{
		return Advanced::class;
	}


	public function has($relationship, $condition = ">", $value = 0)
	{
		$relation = $this->getRelation($relationship);
	}


	protected function getRelationsAlias()
	{
		$relations = $this->getModelsManager()->getRelations(static::class);
		$result = [];
		foreach ($relations as $relation) {
			$result[] = $relation->getOption('alias');
		}
		return $result;
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

	/**
	 * @param $relationAlias
	 * @return Relation
	 */
	protected function getRelation($relationAlias)
	{
		if (!is_string($relationAlias)){
			return $relationAlias;
		}

		return $this->getModelsManager()->getRelationByAlias(static::class, $relationAlias);
	}

	/**
	 * @param $relation
	 * @return Criteria
	 */
	protected function newPivotQuery($relation)
	{
		$relation = $this->getRelation($relation);
		$cls = $relation->getIntermediateModel();
		$query = $cls::query()->where(
			$this->castFieldToString($relation->getIntermediateFields()) . "= :field1:",
			["field1" => $this->getPrimaryKeyValue($relation)]
		);
		return $query;

	}

	protected function getPrimaryKeyValue($relation)
	{
		$relation = $this->getRelation($relation);
		if ($relation instanceof Relation){
			return $this->readAttribute($this->castFieldToString($relation->getFields()));
		}
	}

	public function getPrimaryKeyName($relation)
	{
		$relation = $this->getRelation($relation);
		return $this->castFieldToString($relation->getFields());
	}

	protected function castFieldToString($field)
	{
		if (is_array($field)){
			$field = $field[0];
		}

		return $field;
	}

	protected function parseIds($value)
	{
//		if ($value instanceof Model) {
//			return [$value->getKey()];
//		}

//		if ($value instanceof Collection) {
//			return $value->modelKeys();
//		}

		if ($value instanceof \Phalcon\Support\Collection) {
			return $value->toArray();
		}

		return (array) $value;
	}



}