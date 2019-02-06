<?php

namespace Phalcon\Support;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Application;


class Bootstrap
{
	/**
	 * The Dependency Injector.
	 * @var DiInterface
	 */
	protected $di;

	/**
	 * The Application path.
	 * @var string
	 */
	protected $appPath;

	/**
	 * The Application.
	 * @var Application
	 */
	protected $app;

	protected $loader;

	/**
	 * @var ExceptionHandler
	 */
//    protected $exceptionHandler;

	/**
	 * Bootstrap constructor.
	 *
	 * @param $applicationPath
	 * @param array $providers
	 */
	public function __construct($applicationPath)
	{
		if (!is_dir($applicationPath)) {
			throw new \InvalidArgumentException('The $applicationPath must be a valid application path');
		}

		$this->di = new Di();
		$this->appPath = $applicationPath;

		$this->di->setShared('bootstrap', $this);
		Di::setDefault($this->di);

		$this->loader = new Loader();
	}

	/**
	 * Gets the Dependency Injector.
	 *
	 * @return Di
	 */
	public function getDi()
	{
		return $this->di;
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
	 * Runs the Application
	 *
	 * @return string
	 */
	public function run()
	{
		$this->initLoader();

		$this->initApplication();

		return $this->handleRequest();
	}

	protected function initLoader()
	{
		$this->loader->register();
	}

	/**
	 * Initialize the Service in the Dependency Injector Container.
	 *
	 * @param ServiceProviderInterface $provider
	 *
	 * @return $this
	 */
	public function register($provider)
	{
		if ($provider instanceof \ArrayAccess){
			foreach ($provider as $name => $class) {
				$this->register(new $class($this->getDi()));
			}
			return;
		}

		if ($provider instanceof ServiceProviderInterface){
			$provider->register($this->getDi());
		}
		return $this;
	}

	protected function initApplication()
	{
		$this->app = new Application($this->di);
		$this->app->setEventsManager($this->di->getShared('eventsManager'));
	}

	/**
	 * Get application output.
	 *
	 * @return ResponseInterface
	 */
	protected function handleRequest()
	{
		return $this->app->handle();
	}
}