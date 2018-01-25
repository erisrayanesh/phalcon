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
					$credentials[$this->getPassword()],
					$user->readAttribute($this->getPassword()))) {

				if ($this->isLoginThrottlingEnabled()){
					$this->registerUserThrottling($user->id);
				}
				throw new \Exception('Wrong username/password combination');
			}

			if ($this->isLoginThrottlingEnabled()){
				$this->registerSuccessLogin($user->id);
			}

			auth()->login($user, $user->id, $user->fname);

			// Check if the remember me was selected
			if (isset($credentials['remember']) && $this->isUserRememberEnabled()) {
				$this->createRememberEnvironment($user);
			}

			return true;

		} catch (\Exception $exc) {
			return false;
		}
	}

	protected function getUsername()
	{
		return 'email';
	}

	protected function getPassword()
	{
		return 'password';
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
		$model = auth()->getUserModel();
		return $model->findAuthByUsername($credentials[$this->getUsername()]);
	}

	protected function validateLoginCredential($credentials)
	{
		return true;
	}

	protected function onLoginFailed($credentials)
	{
		return;
	}

	protected function onLoginSuccessful($credentials)
	{
		return;
	}

	protected function onAfterLogout()
	{
		return;
	}


}