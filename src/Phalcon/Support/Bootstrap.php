<?php

namespace Phalcon\Support;

use Phalcon\Config;
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

    protected $providers;

    /**
     * The Service Providers.
     * @var ServiceProviderInterface[]
     */
//    protected $serviceProviders = [];

    /**
     * The Application.
     * @var Application
     */
    protected $app;

    protected $loader;

    /**
     * Bootstrap constructor.
     *
     * @param $applicationPath
     */
    public function __construct($applicationPath, Config $config, array $providers = [])
    {
        if (!is_dir($applicationPath)) {
            throw new \InvalidArgumentException('The $applicationPath must be a valid application path');
        }

        $this->di = new Di();
        $this->appPath = $applicationPath;

        if (count($providers) == 0){
			$providers = $config->application->providers->toArray() ?: [];
		}
        $this->providers = $providers;

        $this->di->setShared('bootstrap', $this);
        Di::setDefault($this->di);

        $this->di->setShared('config', $config);

        $this->initLoader();

        $this->initServiceProviders();

        $this->initApplication();

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
     * Runs the Application
     *
     * @return string
     */
    public function run()
    {
        return $this->handleRequest();
    }

    /**
     * Get application output.
     *
     * @return ResponseInterface
     */
    protected function handleRequest()
    {
		try {
			$response = $this->app->handle();

		} catch (ValidationException $exception){
			$response = $exception->getResponse();
		} catch (AuthorizationException $exception){
			$response = redirect_route('admin.index', null, "error", t("ACCESS_DENIED"));
		}
    }

    protected function initLoader()
	{
		$this->loader = new Loader();
		$this->loader->registerNamespaces([
			'Apps'          => $this->appPath,
		]);
		$this->loader->register();
	}

	protected function initServiceProviders()
	{
		if (count($this->providers)) {
			$this->initServices($this->providers);
		}
	}

	/**
	 * Initialize Services in the Dependency Injector Container.
	 *
	 * @param string[] $providers
	 */
	protected function initServices(array $providers)
	{
		foreach ($providers as $name => $class) {
			$this->initService(new $class($this->di));
		}
	}

	/**
	 * Initialize the Service in the Dependency Injector Container.
	 *
	 * @param ServiceProviderInterface $serviceProvider
	 *
	 * @return $this
	 */
	protected function initService(ServiceProviderInterface $serviceProvider)
	{
		$serviceProvider->register();
//		$this->serviceProviders[$serviceProvider->getName()] = $serviceProvider;

		return $this;
	}

	protected function initApplication()
	{
		$this->app = new Application($this->di);


		$this->app->setEventsManager($this->di->getShared('eventsManager'));
		//$this->app->setDI($this->di);
	}
}
