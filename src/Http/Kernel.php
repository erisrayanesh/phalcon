<?php

namespace Phalcon\Http;

use Phalcon\Bootstrap\Application;
use Phalcon\Debug\ExceptionHandler;
use Phalcon\Debug\FatalThrowableError;

class Kernel implements KernelInterface
{

	/**
	 * @var Application
	 */
	protected  $app;

	public function __construct(Application $application)
	{
		$this->app = $application;
	}

	public function bootstrap()
	{
		$this->initErrorHandlers();

		if (! $this->app->isBooted()) {
			$this->app->bootstrap([]);
		}
	}

	public function handle(RequestInterface $request)
	{
		try {
			$response = $this->handleRequest($request);
		} catch (\Exception $e) {
			$this->reportException($e);
			$response = $this->renderException($e);
		} catch (\Throwable $e) {
			$this->reportException($e = new FatalThrowableError($e));
			$response = $this->renderException($e);
		}

		return $response;
	}

	public function terminate(RequestInterface $request, ResponseInterface $response)
	{
		// TODO: Implement terminate() method.
	}

	public function getApplication()
	{
		return $this->app;
	}

	// ============ EXCEPTION HANDLER

	/**
	 * Handles php errors
	 * @param $level
	 * @param $message
	 * @param string $file
	 * @param int $line
	 * @param array $context
	 * @throws \ErrorException
	 */
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
		return $this->app->get(ExceptionHandler::class);
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

	// ============ EXCEPTION HANDLER

	protected function prepareResponse($value)
	{
		if ($value instanceof ResponseInterface) {
			return $value;
		}

		if (is_bool($value) && $value === false){
			$value = '';
		}

		return response($value);
	}

	protected function callRouteMatchedHandler(RouterInterface $router, RouteInterface $route)
	{
		$match = $route->getMatch();

		if ($match instanceof \Closure) {
			$match = \Closure::bind($match, $this->getDi());
		}

		return call_user_func_array($match, $router->getParams());
	}

	protected function handleRequest(RequestInterface $request)
	{
		$this->app->setShared('request', $request);

		$this->bootstrap();

		router()->handle($uri);
		$matchedRoute = router()->getMatchedRoute();

		if  ($matchedRoute instanceof RouteInterface && $matchedRoute->getMatch() !== null) {
			return $this->prepareResponse($this->callRouteMatchedHandler($matchedRoute));
		}

		$dispatcher = dispatcher();

		$dispatcher->setControllerName(
			router()->getControllerName()
		);

		$dispatcher->setActionName(
			$router->getActionName()
		);

		$dispatcher->setParams(
			$router->getParams()
		);


		$value = $dispatcher->getReturnedValue();
	}


}