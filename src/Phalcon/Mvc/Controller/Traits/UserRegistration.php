<?php

namespace Phalcon\Mvc\Controller\Traits;


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
		try {
			$filter = getSanitizer();

			$user = new Users([
				'fname' => $filter->sanitize($values['fname'],  'striptags'),
				'lname' => $filter->sanitize($values['lname'],  'striptags'),
				'email' => $filter->sanitize($values['email'], 'email'),
				'password' => security()->hash($values['password']),
				'active' => strtoupper(getSecurityRandom()->base58(5)),
				'country_id' => $filter->sanitize($values['country_id'], 'int'),
				'mobile' => $filter->sanitize($values['mobile'], 'int'),
			]);

			if ($user->save() === false){
				throw new \RuntimeException();
			}

			return $user;
		} catch (\RuntimeException $exc) {
			return false;
		}

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