<?php

namespace Phalcon\Auth\Access;

class Guard
{

	protected $abilities = [];

	protected $user;

	public function __construct($user, $abilities = [])
	{
		$this->user = $user;
		$this->abilities = $abilities;
	}

	public function define($ability, $callback)
	{
		if (is_callable($callback)) {
			$this->abilities[$ability] = $callback;
		} elseif (is_string($callback) && str_contains($callback, '::')) {
			$this->abilities[$ability] = $this->generateCallback($callback);
		} else {
			throw new \RuntimeException("Callback must be a callable or a 'Class::method' string");
		}

		return $this;
	}

	public function has($ability)
	{
		return isset($this->abilities[$ability]);
	}

	public function allows($ability, $arguments = [])
	{
		return $this->check($ability, $arguments);
	}

	public function denies($ability, $arguments = [])
	{
		return !$this->allows($ability, $arguments);
	}

	public function check($ability, $arguments = [])
	{
		try {
			return (bool) $this->find($ability, $arguments);
		} catch (AuthorizationException $e) {
			return false;
		}
	}

	public function authorize($ability, $arguments = [])
	{
		$result = $this->find($ability, $arguments);

		if (!$result) {
			throw new AuthorizationException($ability, "Ability unauthorized");
		}

		return $result;
	}

	public function forUser($user)
	{
		return new static($user, $this->abilities);
	}

	public function getUser()
	{
		return $this->user;
	}

	protected function find($ability, $arguments = [])
	{
		if (!$user = $this->getUser()){
			return false;
		}

		$arguments = !is_array($arguments) ? [$arguments] : $arguments;

		$callback = function () {
			return false;
		};

		if (isset($this->abilities[$ability])) {
			$callback = $this->abilities[$ability];
		}

		return $callback($user, ...$arguments);

	}

	protected function generateCallback($callback)
	{
		return function () use ($callback) {
			list($class, $method) = str_parse_callback($callback);
			return call_user_func_array([$class, $method], func_get_args());
		};
	}


}