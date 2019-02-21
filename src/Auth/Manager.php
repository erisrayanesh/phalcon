<?php

namespace Phalcon\Auth;

use Phalcon\Mvc\User\Component;
use Phalcon\Auth\Drivers\Session as SessionGuard;
use Phalcon\Auth\UserProviders\Model as ModelUserProvider;
use Phalcon\Auth\Driver as GuardDriver;

class Manager extends Component
{

	protected $default_guard;

	protected $default_provider;

	/**
	 * System guards
	 * @var array
	 */
	protected $guards = [];

	/**
	 * System user providers
	 * @var array
	 */
	protected $providers = [];

	protected $guardBuilders = [];

	protected $userProviderBuilders = [];

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

	/**
	 * @param null $name
	 * @return GuardDriver
	 */
	public function guard($name = null)
	{
		$name = $name ?: $this->getDefaultGuard();

		if (isset($this->guards[$name]) && $this->guards[$name] instanceof GuardDriver){
			return $this->guards[$name];
		}

		return $this->guards[$name] = $this->resolveGuard($name);
	}

	public function setGuard($name, $guard)
	{
		$this->guards[$name] = $guard;
	}

	public function getDefaultGuard()
	{
		return $this->default_guard;
	}

	public function setDefaultGuard($name)
	{
		$this->default_guard = $name ?: $this->getDefaultGuard();
	}

	public function addGuardBuilder($name, callable $builder)
	{
		$this->guardBuilders[$name] = $builder;
		return $this;
	}

	protected function resolveGuard($guard)
	{
		$config = $this->getGuardConfig($guard);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Auth guard [{$guard}] is not defined.");
		}

		if (isset($this->guardBuilders[$driver = $config['driver']])) {
			return $this->guardBuilders[$driver]($guard, $config);
		}

		switch ($driver) {
			case 'session':
				return $this->createSessionDriver($guard, $config);
			default:
				throw new \InvalidArgumentException("Auth guard driver {$driver} for guard {$guard} is not defined.");
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

	protected function getGuardProvider($name)
	{
		$config = $this->getGuardConfig($name);

		if (is_null($config)) {
			throw new \InvalidArgumentException("Auth guard [{$name}] is not defined.");
		}

		return $config['provider'] ?? null;
	}

	protected function createSessionDriver($guard, $config)
	{
		$provider = $this->createUserProvider( $config['provider'] ?? null);
		return new SessionGuard($guard, $provider);
	}

	// USER PROVIDER

	public function setProvider($name, $provider)
	{
		if (is_null($provider)){
			throw new \InvalidArgumentException('Provider ' . $name . ' can not be null or empty');
		}

		if (empty($provider['driver'] ?? null)) {
			throw new \InvalidArgumentException('No driver specified for provider ' . $name);
		};

		$this->providers[$name] = $provider;
	}

	public function getDefaultProvider()
	{
		return $this->default_provider;
	}

	public function setDefaultProvider($name)
	{
		$this->default_provider = $name ?: $this->getDefaultProvider();
	}

	public function addUserProviderBuilder($name, callable $builder)
	{
		$this->userProviderBuilders[$name] = $builder;
		return $this;
	}

	public function createUserProvider($provider = null)
	{

		if (is_null($config = $this->getProviderConfig($provider))) {
			return;
		}

		if (isset($this->userProviderBuilders[$driver = ($config['driver'] ?? null)])) {
			return call_user_func($this->userProviderBuilders[$driver], $config);
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

	protected function getProviderConfig($provider)
	{
		if ($provider = $provider ?: $this->getDefaultProvider()) {
			return $this->providers[$provider] ?? null;
		}
	}

	protected function createModelProvider($config)
	{
		if (!isset($config['model'])){
			return null;
		}

		return new ModelUserProvider($config['model']);
	}
}