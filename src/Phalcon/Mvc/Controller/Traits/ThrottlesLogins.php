<?php

namespace Phalcon\Mvc\Controller\Traits;


use Apps\LoginAttempts;
use Apps\Users;

trait ThrottlesLogins
{

	protected function hasTooManyLoginAttempts($credentials)
	{
		if (empty($this->getMaxAttempts())){
			return false;
		}

		return $this->getFailedLoginAttemptsCount($credentials, request()->getClientAddress()) >= $this->getMaxAttempts();
	}

	protected  function registerFailedLogin($user_id)
	{
		security()->hash(rand());

		$this->createLoginAttempt(request()->getClientAddress(), request()->getUserAgent(), $user_id);
	}

	protected function registerSuccessLogin($user_id)
	{
		$this->createLoginAttempt(request()->getClientAddress(), request()->getUserAgent(), $user_id, true);
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
	protected function createLoginAttempt($ip, $userAgent, $user_id = 0, $success = false)
	{
		return;
	}

	protected function getMaxAttempts()
	{
		return null;
	}


}