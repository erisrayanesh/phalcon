<?php

namespace Phalcon\Bootstrap;


use Apps\Providers\RouterServiceProvider;
use Phalcon\Di;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Escaper\EscaperServiceProvider;
use Phalcon\Events\EventsServiceProvider;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterServiceProvider;
use Phalcon\Http\Response\ResponseServiceProvider;
use Phalcon\Logger\Manager as LogManager;
use Phalcon\Loader;
use Phalcon\Logger\Providers\LogServiceProvider;
use Phalcon\Mvc\Dispatcher\DispatcherServiceProvider;
use Phalcon\Mvc\Model\MetaData\ModelMetaDataServiceProvider;
use Phalcon\Mvc\Model\ModelManagerServiceProvider;
use Phalcon\Mvc\Url\UrlServiceProvider;
use Phalcon\Security\SecurityServiceProvider;
use Phalcon\Session\SessionServiceProvider;


class Application extends Di
{

	/**
	 * The Application path.
	 * @var string
	 */
	protected $appPath;

	protected $loader;

	protected $serviceProviders;

	protected $bootstrapped = false;

	protected $booted = false;


	/**
	 * Application constructor.
	 *
	 * @param $applicationPath
	 */
	public function __construct($applicationPath)
	{
		parent::__construct();

		if  (!is_null($applicationPath)) {
			$this->setAppPath($applicationPath);
		}

		$this->registerSelf();

		$this->registerBaseServiceProviders();

		$this->loader = new Loader();
	}

	/**
	 * @param string $appPath
	 */
	public function setAppPath($appPath)
	{
		if (!is_dir($appPath)) {
			throw new \InvalidArgumentException('The $applicationPath must be a valid application path');
		}

		$this->appPath = $appPath;
	}

	/**
	 * Gets the Application path.
	 *
	 * @return string
	 */
	public function getAppPath()
	{
		return $this->appPath;
	}

	/**
	 * @return mixed
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 * Initialize the Service in the Dependency Injector Container.
	 *
	 * @param ServiceProviderInterface $provider
	 *
	 * @return ServiceProviderInterface
	 */
	public function register(ServiceProviderInterface $provider)
	{

		if (($registered = $this->getProvider($provider))) {
			return $registered;
		}

		$provider->register($this);

		$this->serviceProviders[] = $provider;

		if ($this->isBooted()) {
			$this->bootProvider($provider);
		}

		return $provider;
	}

	public function setupProviders($providers)
	{
		foreach ($providers as $provider) {
			$this->register($provider);
		}
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->isBooted()) {
			return;
		}

		array_walk($this->getServices(), function ($p) {
			$this->bootProvider($p);
		});

		$this->booted = true;

	}

	/**
	 * Indicates that application with it's providers are loaded
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	/**
	 * Indicates that the application kernel is booted
	 * @return bool
	 */
	public function isBootstrapped(): bool
	{
		return $this->bootstrapped;
	}

	public function bootstrap(array $bootstrappers = [])
	{
		foreach ($bootstrappers as $bootstrapper) {

			if (is_callable($bootstrapper) || $bootstrapper instanceof \Closure) {
				call_user_func($bootstrapper, $this);
			}

			if (is_string($bootstrapper)) {
				(new $bootstrapper)->bootstrap($this);
			}

		}

		$this->bootstrapped = true;
	}

	public function resolveProvider($provider)
	{
		return new $provider($this);
	}

	/**
	 * Get the registered service provider instance if it exists.
	 *
	 * @param  ServiceProviderInterface|string  $provider
	 * @return ServiceProviderInterface|null
	 */
	public function getProvider($provider)
	{
		return array_values($this->getProviders($provider))[0] ?? null;
	}

	/**
	 * Get the registered service provider instances if any exist.
	 *
	 * @param  ServiceProviderInterface|string  $provider
	 * @return array
	 */
	public function getProviders($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);

		return array_where($this->serviceProviders, function ($value) use ($name) {
			return $value instanceof $name;
		});
	}

	protected function initLoader()
	{
		$this->loader->register();
	}

	protected function registerSelf()
	{
		$this->setShared('app', $this);
		static::setDefault($this);
	}

	protected function registerBaseServiceProviders()
	{
		$this->register(new EventsServiceProvider());
		$this->register( new LogServiceProvider());
		$this->register(new RouterServiceProvider());
		$this->register(new UrlServiceProvider());
		$this->register(new DispatcherServiceProvider());
		$this->register(new ResponseServiceProvider());
		$this->register(new EscaperServiceProvider());
		$this->register(new FilterServiceProvider());
		$this->register(new ModelManagerServiceProvider());
		//$this->register(new ModelMetaDataServiceProvider());

	}

	protected function bootProvider(ServiceProviderInterface $provider)
	{
		if (method_exists($provider, 'boot')) {
			call_user_func([$provider, 'boot']);
		}
	}


}