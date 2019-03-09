<?php

namespace Phalcon\Mvc\Controller;


use Apps\Users;
use Phalcon\Mvc\Model;

trait UserRegistration
{

	protected function attemptRegister($values)
	{

		if (!$this->validateRegistration($values)){
			return $this->onRegistrationFailed($values);
		}

		if (!$user = $this->createUser($values)){
			return $this->onRegistrationFailed($values);
		}

		return $this->onAfterRegistration($user);

	}

	protected function validateRegistration($values)
	{
		return true;
	}

	protected function createUser($values)
	{
		return null;
	}

	protected function onRegistrationFailed($data)
	{
		return;
	}

	protected function onAfterRegistration(Model $user)
	{
		return;
	}


}