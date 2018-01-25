<?php

namespace Phalcon\Mvc\Model\Traits;


trait Authenticatable
{
	public function getAuthName()
	{
		return $this->name;
	}
}