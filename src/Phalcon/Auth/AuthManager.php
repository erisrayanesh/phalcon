<?php

namespace Phalcon\Auth;

use Phalcon\Auth\Access\Guard;
use Phalcon\Mvc\User\Component;

class AuthManager extends Component
{

	protected $default;

	/**
	 * System guards
	 * @var array
	 */
	protected $guards = [];


	public function __construct()
	{

	}

	public function guard($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();
		return $this->guards[$name];
	}

	public function setGuard($name, $guard)
	{
		$this->guards[$name] = $guard;
	}

	public function getDefaultGuard()
	{
		return $this->default;
	}

	public function setDefaultGuard($default)
	{
		$this->default = $default;
	}


	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return $this->guard()->{$method}(...$parameters);
	}
}
