<?php

namespace Phalcon\Mvc\Controller\Traits;


use Apps\Users;
use Phalcon\Mvc\Model;

trait AuthenticatesUsers
{

	protected function login($credentials = null)
	{

		$credentials = $credentials ?: $this->getLoginCredentials();

		if (!$this->validateLoginCredentials($credentials)){
			return $this->onLoginFailed($credentials);
		}

		if ($this->isLoginThrottlingEnabled()){
			if ($this->hasTooManyLoginAttempts($credentials)){
				return $this->onLoginThrottled($credentials);
			}
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
					$this->registerFailedLogin(0);
				}
				throw new \Exception('user not found');
			}

			// Check the password
			$password = $credentials[$this->getPasswordKey()] . $this->getPasswordSalt();
			$hashPassword = $user->{$this->getPasswordKey()};
			if (!security()->checkHash($password, $hashPassword)) {

				if ($this->isLoginThrottlingEnabled()){
					$this->registerFailedLogin($user->id);
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
		return method_exists($this, 'hasTooManyLoginAttempts');
	}

	protected function isUserRememberEnabled()
	{
		return method_exists($this, 'createRememberEnvironment');
	}

	protected function findUser($credentials)
	{
		return auth()->findUserByUsername($credentials[$this->getUsernameKey()]);
	}

	protected function getLoginCredentials()
	{
		return request_only([
			$this->getUsernameKey(),
			$this->getPasswordKey()
		]);
	}

	protected function getPasswordSalt()
	{
		return 'jFr!!A&+71w1Ms9~8';
	}

	protected function validateLoginCredentials($credentials)
	{
		return true;
	}

	protected function onLoginFailed($credentials)
	{
		return;
	}

	protected function onLoginSuccessful($user)
	{
		session()->regenerateId();
		return $this->onAuthenticated($user);
	}

	protected function onAuthenticated($user)
	{
		return;
	}

	protected function onAfterLogout()
	{
		return;
	}

	protected function onLoginThrottled($credentials)
	{
		return;
	}


}