<?php

namespace Phalcon\Auth;

use Phalcon\Mvc\User\Component;
use Phalcon\Auth\Drivers\Session as SessionGuard;
use Phalcon\Auth\UserResolvers\Model as ModelResolver;

class AuthManager extends Component
{

	protected $default;

	/**
	 * System guards
	 * @var array
	 */
	protected $guards = [];

	protected $guardBuilders = [];

	protected $userResolverBuilders = [];

	public function __construct()
	{

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

	public function guard($name = null)
	{
		$name = $name ?: $this->getDefaultGuard();

		if (isset($this->guards[$name]) && !is_array($this->guards[$name])){
			return $this->guards[$name];
		}

		return $this->guards[$name] = $this->resolveGuard($name);;
	}

	public function setGuard($name, $driver, array $provider)
	{
		$this->guards[$name] = [
			"driver" => $driver,
			"provider" => $provider,
		];
	}

	public function getDefaultGuard()
	{
		return $this->default;
	}

	public function setDefaultGuard($name)
	{
		$this->default = $name ?: $this->getDefaultGuard();
	}

	public function addGuardBuilder($name, callable $builder)
	{
		$this->guardBuilders[$name] = $builder;
		return $this;
	}

	public function addGuardBuilders($builders)
	{
		foreach ($builders as $key => $builder) {
			$this->addGuardBuilder($key, $builder);
		}
		return $this;
	}

	public function createUserResolver($config = null)
	{
		if (isset($this->userResolverBuilders[$driver = ($config['driver'] ?? null)])) {
			return call_user_func($this->userResolverBuilders[$driver], $config);
		}

		switch ($driver) {
			case 'model':
				return $this->createModelProvider($config);
			default:
				throw new \InvalidArgumentException(
					"Authentication user provider {$driver} is not defined."
				);
		}
	}

	protected function resolveGuard($name)
	{
		$config = $this->getGuardConfig($name);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Auth guard [{$name}] is not defined.");
		}

		if (isset($this->guardBuilders[$driver = $config['driver']])) {
			return $this->callGuardBuilder($name, $config);
		}

		switch ($driver) {
			case 'session':
				return $this->createSessionDriver($name, $config);
			default:
				throw new \InvalidArgumentException("Auth guard driver {$driver} for guard {$name} is not defined.");
		}

	}

	protected function getGuardConfig($name)
	{
		if (!isset($this->guards[$name])){
			return null;
		}

		if (!is_array($this->guards[$name])){
			return null;
		}

		return $this->guards[$name];
	}

	protected function callGuardBuilder($name, $config)
	{
		return $this->guardBuilders[$config['driver']]($name, $config);
	}

	protected function createSessionDriver($key, $config)
	{
		$resolver = $this->createUserResolver($config['provider'] ?? null);
		return new SessionGuard($key, $resolver);
	}

	protected function createModelProvider($config)
	{
		if (!isset($config['model'])){
			return null;
		}

		return new ModelResolver($config['model']);
	}
}