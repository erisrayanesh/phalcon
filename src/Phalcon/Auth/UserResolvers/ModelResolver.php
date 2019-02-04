<?php

namespace Phalcon\Auth\UserResolvers;

use Phalcon\Auth\Authenticatable;

class ModelResolver implements UserResolver
{
	public function findById($identifier)
	{
		// TODO: Implement findById() method.
	}

	public function findByToken($identifier, $token)
	{
		// TODO: Implement findByToken() method.
	}

	public function updateRememberToken(Authenticatable $user, $token)
	{
		// TODO: Implement updateRememberToken() method.
	}

	public function findByCredentials(array $credentials)
	{
		// TODO: Implement findByCredentials() method.
	}

	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		// TODO: Implement validateCredentials() method.
	}


	/**
	 * @return string
	 */
	public function getUserModel()
	{
		return $this->userModel;
	}

	/**
	 * @param string $userModel
	 * @return Auth
	 */
	public function setUserModel($userModel)
	{
		$this->userModel = $userModel;
		return $this;
	}

	public function findUserById($id)
	{
		$instance = $this->getUserInstance();
		$method = "findFirstBy" . camelize($this->getIdentityKey());
		return $instance->{$method}($id);
	}

	public function findUserByUsername($username)
	{
		$instance = $this->getUserInstance();
		$method = "findFirstBy" . camelize($this->getUsernameKey());
		return $instance->{$method}($username);
	}


}