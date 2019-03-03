<?php

namespace Phalcon\Bootstrap;


use Phalcon\Di;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Manager as LogManager;
use Phalcon\Loader;


class Application extends Di
{

	/**
	 * The Application path.
	 * @var string
	 */
	protected $appPath;

	protected $loader;

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
	 * @return $this
	 */
	public function register(ServiceProviderInterface $provider)
	{
		if (array_accessible($provider)) {
			foreach ($provider as $name => $class) {
				$this->register(new $class($this));
			}
			return $this;
		}

		if ($provider instanceof ServiceProviderInterface){
			$provider->register($this);
		}
		return $this;
	}

	/**
	 * Indicates that application with it's providers are loaded
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	public function bootstrap(array $bootstrappers = null)
	{
		$this->bootstrapped = true;
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
		$this->setShared('eventsManager', new EventsManager());
		$this->setShared('eventsManager', new LogManager());
	}

}