<?php

namespace Phalcon\Mvc\Controller\Traits;


use Apps\Users;
use Phalcon\Mvc\Model;

trait AuthenticatesUsers
{

	protected function login($credentials)
	{

		if (!$this->validateLoginCredential($credentials)){
			return $this->onLoginFailed($credentials);
		}

		return $this->attemptLogin($credentials);

	}

	protected function logout()
	{
		if ($this->isUserRememberEnabled()){
			$this->terminateRemember();
		}

		auth()->logout();
		$this->onAfterLogout();
	}



	protected function attemptLogin($credentials)
	{
		try {
			// Check if the user exist
			$user = $this->findUser($credentials);

			if ($user == false) {
				if ($this->isLoginThrottlingEnabled()){
					$this->registerUserThrottling(0);
				}
				throw new \Exception('user not found');
			}

			// Check the password
			if (!security()->checkHash(
					$credentials[$this->getPasswordKey()],
					$user->{$this->getPasswordKey()})) {

				if ($this->isLoginThrottlingEnabled()){
					$this->registerUserThrottling($user->id);
				}
				throw new \Exception('Wrong username/password combination');
			}

			if ($this->isLoginThrottlingEnabled()){
				$this->registerSuccessLogin($user->id);
			}

			auth()->login($user);

			// Check if the remember me was selected
			if (isset($credentials['remember']) && $this->isUserRememberEnabled()) {
				$this->createRememberEnvironment($user);
			}

			return $this->onLoginSuccessful($user);

		} catch (\Exception $exc) {
			return $this->onLoginFailed($credentials);
		}
	}

	protected function getUsernameKey()
	{
		return auth()->getUsernameKey();
	}

	protected function getPasswordKey()
	{
		return auth()->getPasswordKey();
	}

	protected function isLoginThrottlingEnabled()
	{
		return method_exists($this, 'registerUserThrottling');
	}

	protected function isUserRememberEnabled()
	{
		return method_exists($this, 'createRememberEnvironment');
	}

	protected function findUser($credentials)
	{
		return auth()->findUserByUsername($credentials[$this->getUsernameKey()]);
	}

	protected function validateLoginCredential($credentials)
	{
		return true;
	}

	protected function onLoginFailed($credentials)
	{
		return;
	}

	protected function onLoginSuccessful($user)
	{
		return;
	}

	protected function onAfterLogout()
	{
		return;
	}


}