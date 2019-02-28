<?php

namespace Phalcon;

use Phalcon\Debug\ExceptionHandler;
use Phalcon\Debug\FatalThrowableError;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
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
			$e = new FatalThrowableError($e);
		}

		try {
			$this->getExceptionHandler()->report($e);
			$this->renderException($e)->send();;
			return;
		} catch (\Exception $exception) {
			echo "Sorry. Something went wrong...";
		}

	}

	protected function initApplication()
	{
		$this->app = new Application($this->di);
		$this->app->setEventsManager($this->di->getShared('eventsManager'))
			->sendHeadersOnHandleRequest(false)
			->sendCookiesOnHandleRequest(false);
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
		} catch (\Exception $e) {
			$this->reportException($e);
			$response = $this->renderException($e);
		} catch (\Throwable $e) {
			$this->reportException($e = new FatalThrowableError($e));
			$response = $this->renderException($e);
		}

		return $response;
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

	/**
	 * @return ExceptionHandler|null
	 */
	protected function getExceptionHandler()
	{
		return $this->getDi()->get(ExceptionHandler::class);
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function reportException(\Exception $e)
	{
		$this->getExceptionHandler()->report($e);
	}

	/**
	 * Render the exception to a response.
	 *
	 * @param  \Exception  $e
	 * @return ResponseInterface
	 */
	protected function renderException(\Exception $e)
	{
		return $this->getExceptionHandler()->render($e);
	}

}