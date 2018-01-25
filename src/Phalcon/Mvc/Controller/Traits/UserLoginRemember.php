<?php

namespace Phalcon\Mvc\Controller\Traits;


use Apps\Users;
use Phalcon\Mvc\Model;

trait UserLoginRemember
{

	protected function isLoginThrottlingEnabled()
	{
		return method_exists($this, 'registerUserThrottling');
	}

	protected function setupRememberCookie($rmu, $rmt, $expire)
	{
		$this->cookies->set('RMU', $rmu, $expire);
		$this->cookies->set('RMT', $rmt, $expire);
	}

	/**
	 * Creates the remember me environment settings the related cookies and generating tokens
	 *
	 * @param Model $user
	 */
	protected function createRememberEnvironment(Model $user)
	{
		$userAgent = request()->getUserAgent();
		$token = $this->generateRememberToken($user->readAttribute($this->getUsername()));

		$remember = new Remembers();
		$remember->user_id = $user->id;
		$remember->token = $token;
		$remember->user_agent = $userAgent;

		if ($remember->save() != false) {
			$this->setupRememberCookie($user->id, $token, time() + 86400 * 8);
		}
	}

	protected function generateRememberToken($username, $userAgent = null)
	{
		return md5($username . ($userAgent ?: request()->getUserAgent()));
	}

	/**
	 * Check if the session has a remember me cookie
	 *
	 * @return boolean
	 */
	protected function hasRememberMe()
	{
		return $this->cookies->has('RMU');
	}

	/**
	 * Logs on using the information in the cookies
	 *
	 * @return \Phalcon\Http\Response
	 */
	protected function attemptLoginWithRememberMe()
	{
		$userId = $this->cookies->get('RMU')->getValue();
		$cookieToken = $this->cookies->get('RMT')->getValue();

		$user = Users::findFirstById($userId);
		if (!$user) {
			$this->terminateRemember();
			return false;
		}

		$token = $this->generateRememberToken();

		if ($cookieToken !== $token) {
			$this->terminateRemember();
			return false;
		}

		$remember = Remembers::findFirst([
			'user_id = ?0 AND token = ?1',
			'bind' => [
				$user->id,
				$token
			]
		]);

		if (!$remember) {
			$this->terminateRemember();
			return false;
		}

		// Check if the cookie has not expired
		if ((time() - (86400 * 8)) > $remember->created_at) {
			$this->terminateRemember();
			return false;
		}

		// Register identity
		$this->setSessionIdentity($user);

		// Register the successful login
		$this->registerSuccessLogin($user);

		return true;
	}

	protected function terminateRemember()
	{
		if ($this->hasRememberMe()) {
			$this->cookies->get('RMU')->delete();
		}

		if ($this->cookies->has('RMT')) {

			$token = $this->cookies->get('RMT')->getValue();

			$remember = Remember::findFirst(
				[
					"token = ?1",
					'bind' => [
						1 => $token
					]
				]
			);

			if ($remember){
				$remember->delete();
			}

			$this->cookies->get('RMT')->delete();
		}
	}

}