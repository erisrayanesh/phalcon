<?php

namespace Phalcon\Mvc\Model\Traits;


use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple;

trait HasAttributes
{

	protected $appends = [];

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

	public function getAllAppendedAttributeValues()
	{
		$arr = [];
		foreach ($this->appends as $attribute){
			$arr[$attribute] = $this->__get($attribute);
		}
		return $arr;
	}


}