<?php

namespace Phalcon\Mvc\Controller\Traits;


use Apps\LoginAttempts;
use Apps\Users;

trait UserLoginThrottling
{

	/**
	 * Implements login throttling
	 * Reduces the effectiveness of brute force attacks
	 *
	 * @param int $user_id
	 */
	protected  function registerUserThrottling($user_id)
	{
		security()->hash(rand());

		$failedLogin = $this->createLoginAttempt($user_id);
		$failedLogin->save();

//        $attempts = $this->callModelMethod('login','count',
//			[
//            	'ip = :ip: AND attempted_at >= :attempt_id: AND success = 1',
//            	'bind' => [
//                	'ip' => $this->request->getClientAddress(),
//                	'attempt_id' => time() - 3600 * 6
//            	]
//        	]
//		);
//
//        switch ($attempts) {
//            case 1:
//            case 2:
//                // no delay
//                break;
//            case 3:
//            case 4:
//                sleep(2);
//                break;
//            default:
//                sleep(4);
//                break;
//        }
	}

	/**
	 * Creates the remember me environment settings the related cookies and generating tokens
	 *
	 * @param int $user_id
	 * @throws Exception
	 */
	protected function registerSuccessLogin($user_id)
	{
		$successLogin = $this->createLoginAttempt($user_id, true);
		if (!$successLogin->save()) {
			$messages = $successLogin->getMessages();
			throw new \Exception($messages[0]);
		}
	}

	/**
	 * Creates new login attempt
	 * @param int $user_id
	 * @param bool $success
	 * @return Model
	 */
	protected function createLoginAttempt($user_id = 0, $success = false)
	{
		$loginAttempt = new LoginAttempts();
		$loginAttempt->user_id = $user_id;
		$loginAttempt->ip = request()->getClientAddress();
		$loginAttempt->user_agent = request()->getUserAgent();
		$loginAttempt->success = $success;
		return $loginAttempt;
	}


}