<?php

namespace Phalcon\Http;

use Phalcon\Bootstrap\Application;
use Phalcon\Debug\ExceptionHandler;
use Phalcon\Debug\FatalThrowableError;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Router\RouteInterface;
use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class Kernel implements KernelInterface
{

	/**
	 * @var Application
	 */
	protected  $app;

	protected $bootstrappers = [];

	public function __construct(Application $application)
	{
		$this->app = $application;
		$this->bootstrappers[] = [$this, 'initErrorHandlers'];
	}

	public function bootstrap()
	{
		if (! $this->app->isBooted()) {
			$this->app->bootstrap($this->bootstrappers);
		}

		$this->app->boot();
	}

	public function handle(FormRequest $request)
	{
		try {
			$request->setHttpMethodParameterOverride(true);
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

	public function terminate(FormRequest $request, ResponseInterface $response)
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



	protected function handleRequest(FormRequest $request)
	{
		$this->app->setShared('request', $request);

		$this->bootstrap();

		// Call boot event
		if ($this->fireBootEvent() === false) {
			return false;
		}

		if (! ($response = $this->dispatch($request)) instanceof ResponseInterface){
			$response = $this->prepareResponse($request, $response);
		}

		// Call beforeSendResponse
		$this->fireBeforeSendResponse($response);

		return $response;
	}

	/**
	 * Dispatches request through router
	 * @param FormRequest $request
	 * @return mixed|string
	 * @throws \Exception
	 */
	protected function dispatch(FormRequest $request)
	{

		$matchedRoute = $this->getMatchedRoute($request);

		if  ($matchedRoute instanceof RouteInterface && $matchedRoute->getMatch() !== null) {
			$response = $this->callMatchedRouteHandler($matchedRoute);
			if (is_string($response) || $response instanceof ResponseInterface){
				return $response;
			}
		}


		//Start the view here because later we add events,
		// so that it's possible for theme to render something into the view
		$view = view();
		$view->start();

		$router = router();
		$dispatcher = dispatcher();
		$dispatcher->setModuleName($router->getModuleName());
		$dispatcher->setNamespaceName($router->getNamespaceName());
		$dispatcher->setControllerName($router->getControllerName());
		$dispatcher->setActionName($router->getActionName());
		$dispatcher->setParams($router->getParams());

		// Calling beforeHandleRequest
		$this->fireBeforeHandleRequestEvent($dispatcher);

		$controller = $dispatcher->dispatch();
		$possibleResponse = $dispatcher->getReturnedValue();

		$renderView  = $possibleResponse !== false
							&& ! is_string($possibleResponse)
							&& ! $possibleResponse instanceof ResponseInterface
							&& is_object($controller);

		if ($renderView) {
			// Calling afterHandleRequest
			$this->fireAfterHandleRequestEvent($controller);

			$renderStatus = $this->fireViewRender($view);

			// Check if the view process has been treated by the developer
			if ($renderStatus !== false) {
				$view->render($dispatcher->getControllerName(), $dispatcher->getActionName());
			}
		}

		$view->finish();

		if ($renderView) {
			$possibleResponse = $view->getContent();
		}

		return $possibleResponse;
	}

	/**
	 * @param FormRequest $request
	 * @return RouteInterface
	 */
	protected function getMatchedRoute(FormRequest $request)
	{
		router()->handle();
		return router()->getMatchedRoute();
	}

	protected function callMatchedRouteHandler(RouteInterface $route)
	{
		$match = $route->getMatch();

		if ($match instanceof \Closure) {
			$match = \Closure::bind($match, $this->app);
		}

		return call_user_func_array($match, router()->getParams());
	}

	protected function prepareResponse(FormRequest $request, $response)
	{
		if ($response instanceof ResponseInterface) {
			return $response;
		}

		if (is_bool($response) && $response === false){
			$response = '';
		}

		if ($response instanceof Model) {
			$response = new JsonResponse($response, 201);
		} elseif (! $response instanceof Response &&
			($response instanceof Arrayable ||
				$response instanceof Jsonable ||
				$response instanceof \ArrayObject ||
				$response instanceof \JsonSerializable ||
				is_array($response))) {
			$response = new JsonResponse($response);
		} elseif (! $response instanceof Response) {
			$response = new Response($response);
		}

		return $response;
	}

	protected function fireBootEvent()
	{
		return $this->app->getEventsManager()->fire("application:boot", $this) ;
	}

	protected function fireBeforeHandleRequestEvent($dispatcher)
	{
		return $this->app->getEventsManager()->fire("application:beforeHandleRequest", $this, $dispatcher) ;
	}

	protected function fireAfterHandleRequestEvent($controller)
	{
		$this->app->getEventsManager()->fire("application:afterHandleRequest", $this, $controller) ;
	}

	protected function fireViewRender($view)
	{
		return $this->app->getEventsManager()->fire("application:viewRender", $this, $view) ;
	}

	protected function fireBeforeSendResponse($response)
	{
		$this->app->getEventsManager()->fire("application:beforeSendResponse", $this, $response) ;
	}
}