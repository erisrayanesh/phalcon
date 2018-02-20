<?php

namespace Phalcon\Mvc\Model\Traits;


use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple;

trait HasAttributes
{

	protected $appends = [];

	public function toArray($columns = null)
	{
		$arr = parent::toArray($columns);
		foreach ($this->appends as $attribute){
			$arr[$attribute] = $this->getAppendedAttributeValue($attribute);
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

	public function hasAppended($attribute)
	{
		$method = "get" . camelize($attribute);
		if (in_array($attribute, $this->appends) && method_exists($this, $method)){
			return true;
		}
		return false;
	}

	public function readAttribute($attribute)
	{
		if ($this->__isset($attribute)){
			return $this->getAppendedAttributeValue ($attribute);
		}

		return parent::readAttribute($attribute);
	}

	public function __isset($attribute)
	{
		if ($this->hasAppended($attribute)){
			return true;
		}
		return parent::__isset($attribute);
	}

	public static function findAndCollect($parameters = null)
	{
		$resultset = parent::find($parameters);

		if ($resultset instanceof \Phalcon\Mvc\Model\Resultset){
			$resultset = collect($resultset);
		}

		return $resultset;
	}

	protected function getAppendedAttributeValue($attribute)
	{
		return $this->__get($attribute);
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