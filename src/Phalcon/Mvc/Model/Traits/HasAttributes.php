<?php

namespace Phalcon\Mvc\Model\Traits;


trait HasAttributes
{

	protected $appends = [];

	public function toArray($columns = null)
	{
		$arr = parent::toArray($columns);
		foreach ($this->appends as $appended){
			$arr[$appended] = $this->{$appended};
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
		if ($this->hasAppended($attribute)){
			$method = "get" . camelize($attribute);
			return call_user_func([$this, $method]);
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
}