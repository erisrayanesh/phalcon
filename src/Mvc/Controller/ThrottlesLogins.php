<?php

namespace Phalcon\Mvc\Controller;


use Phalcon\Validation\ValidationException;

trait ThrottlesLogins
{

	protected function hasTooManyLoginAttempts($credentials)
	{
		if (empty($this->getMaxAttempts())){
			return false;
		}

		return $this->getFailedLoginAttemptsCount($credentials, request()->getClientAddress()) >= $this->getMaxAttempts();
	}

	protected function registerFailedLogin($credentials = null)
	{
		$this->createLoginAttempt(request()->getClientAddress(), request()->getUserAgent(), $credentials);
	}

	protected function registerSuccessfulLogin($credentials, $forgetFailedAttempts = true)
	{
		if ($forgetFailedAttempts){
			$this->forgetLoginAttempts($credentials);
		}

		$this->createLoginAttempt(request()->getClientAddress(), request()->getUserAgent(), $credentials, true);
	}

	protected function forgetLoginAttempts($credentials)
	{
		$this->clearLoginAttempts($credentials, request()->getClientAddress());
	}

	protected function getFailedLoginAttemptsCount($credentials, $ip)
	{
		return 0;
	}

	/**
	 * Creates new login attempt
	 * @param string $ip
	 * @param string $userAgent
	 * @param int $user_id
	 * @param bool $success
	 */
	protected function createLoginAttempt($credentials, $ip, $userAgent, $successful = false)
	{
		return;
	}

	protected function clearLoginAttempts($credentials, $ip)
	{
		return;
	}

	protected function getMaxAttempts()
	{
		return 5;
	}

	protected function onLoginThrottled($credentials)
	{
		throw new ValidationException(validator([]));
	}


}