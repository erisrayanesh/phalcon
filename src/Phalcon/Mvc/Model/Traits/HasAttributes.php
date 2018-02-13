<?php

namespace Phalcon\Mvc\Model\Traits;


trait HasAttributes
{

	protected $appends = [];

	public function toArray($columns = null)
	{
		$arr = parent::toArray($columns);
		foreach ($this->appends as $attribute){
			$arr[$attribute] = $this->getAppendedAttributeValue($attribute);
		}
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

	protected function getAppendedAttributeValue($attribute)
	{
		return $this->__get($attribute);
	}

	public static function findAndCollect($parameters = null)
	{
		$resultset = parent::find($parameters);

		if ($resultset instanceof \Phalcon\Mvc\Model\Resultset){
			$resultset = collect($resultset);
		}

		return $resultset;
	}
}