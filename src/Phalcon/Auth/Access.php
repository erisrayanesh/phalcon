<?php

namespace Phalcon\Auth;

use Phalcon\Auth\Access\AuthorizationException;

class Access
{

	protected $abilities = [];

	/**
	 * @var callable
	 */
	protected $userResolver;

	public function __construct(callable $userResolver, $abilities = [])
	{
		$this->userResolver = $userResolver;
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

	public function check($abilities, $arguments = [])
	{
		return collect($abilities)->every(function ($ability) use ($arguments){
			try {
				return (bool) $this->find($ability, $arguments);
			} catch (AuthorizationException $e) {
				return false;
			}
		});
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
		$callback = function () use ($user) {
			return $user;
		};

		return new static($callback, $this->abilities);
	}

	public function getUserResolver()
	{
		return $this->userResolver;
	}

	protected function find($ability, $arguments = [])
	{
		$user = $this->resolveUser();

		$arguments = !is_array($arguments) ? [$arguments] : $arguments;

		$callback = function () {
			return false;
		};

		if ($this->has($ability)) {
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

	protected function resolveUser()
	{
		return call_user_func($this->userResolver);
	}

}