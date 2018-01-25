<?php

namespace Phalcon\Mvc\Model\Traits;


trait Authenticatable
{

	public function findAuthenticatable($id)
	{
		$id = $this->getAuthenticatableIdentityKey();
		return $this->findFirstById($id);
	}

	public function getAuthenticatableName()
	{
		return $this->name;
	}

	public function getAuthenticatableIdentityKey()
	{
		$id = $this->getModelsMetaData()->getPrimaryKeyAttributes($this);
		return $id[0];
	}

	public function getAuthenticatableIdentity()
	{
		if ($this->_exists()){
			$id = $this->getAuthenticatableIdentityKey();
			return $this->readAttribute($id);
		}
	}

}