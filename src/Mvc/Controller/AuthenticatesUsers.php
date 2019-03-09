<?php

namespace Phalcon\Mvc\Controller;

use Phalcon\Validation\ValidationException;

trait AuthenticatesUsers
{
	use ThrottlesLogins;

	public function loginAction()
	{

		$credentials = $this->getLoginCredentials();

		// Validates credential and if invalid then throws an ValidationException
		$this->validateLogin($credentials);

		if ($this->hasTooManyLoginAttempts($credentials)) {
			return $this->onLoginThrottled($credentials);
		}

		if ($this->attemptLogin($credentials)){
			return $this->sendLoginResponse($credentials);
		}

		$this->registerFailedLogin($credentials);

		return $this->sendFailedLoginResponse($credentials);

	}

	public function logoutAction()
	{
		$this->guard()->logout();
		return $this->loggedOut();
	}

	public function username()
	{
		return 'email';
	}

	public function shouldRemember()
	{
		return false;
	}

	protected function getLoginCredentials()
	{
		return request_only($this->username(), 'password');
	}

	protected function attemptLogin($credentials)
	{
		return $this->guard()->attempt($credentials, $this->shouldRemember());
	}

	protected function guard()
	{
		return auth()->guard();
	}

	protected function validateLogin($credentials)
	{
		$validator = validator($this->getRules(), $credentials);
		if ($validator->getMessages()->count() > 0){
			throw new ValidationException($validator);
		}
	}

	protected function getRules()
	{
		return [
			[$this->username(), new \Phalcon\Validation\Validator\PresenceOf()],
			["password", new \Phalcon\Validation\Validator\PresenceOf()],
		];
	}

	protected function sendLoginResponse($credentials)
	{
		session()->regenerateId(false);
		$this->registerSuccessfulLogin($credentials);
		return $this->authenticated($this->guard()->user());
	}

	// INTERNAL EVENTS

	protected function sendFailedLoginResponse($credentials)
	{
		throw new ValidationException(validator([]));
	}

	protected function authenticated($user)
	{
		return;
	}

	protected function loggedOut()
	{
		return;
	}

}