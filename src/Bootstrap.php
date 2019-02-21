<?php

namespace Phalcon;

use Phalcon\Debug\ExceptionHandler;
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
	 * Bootstrap constructor.
	 *
	 * @param $applicationPath
	 */
	public function __construct($applicationPath)
	{
		$this->initErrorHandlers();

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
		if (array_accessible($provider)) {
			foreach ($provider as $name => $class) {
				$this->register(new $class($this->getDi()));
			}
			return $this;
		}

		if ($provider instanceof ServiceProviderInterface){
			$provider->register($this->getDi());
		}
		return $this;
	}

	public function handleError($level, $message, $file = '', $line = 0, $context = [])
	{
		if (error_reporting() & $level) {
			throw new \ErrorException($message, 0, $level, $file, $line);
		}
	}

	public function handleShutdown()
	{
		if (!is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
			$this->handleException(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}

	public function handleException($e)
	{
		if (!$e instanceof \Exception) {
			if ($e instanceof \ParseError) {
				$severity = E_PARSE;
			} elseif ($e instanceof \TypeError) {
				$severity = E_RECOVERABLE_ERROR;
			} else {
				$severity = E_ERROR;
			}

			$e = new \ErrorException($e->getMessage(), $e->getCode(), $severity, $e->getFile(), $e->getLine(), $e->getPrevious());
		}

		if ($this->getDi()->has(ExceptionHandler::class)){
			$this->getDi()->get(ExceptionHandler::class)->render($e)->send();
		}

		echo "Sorry. Something went wrong...";
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

	protected function initErrorHandlers()
	{
		error_reporting(-1);
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);
		register_shutdown_function([$this, 'handleShutdown']);
		ini_set('display_errors', 'Off');
	}

	protected function isFatalError($type)
	{
		return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
	}

}