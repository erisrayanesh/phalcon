<?php

namespace Phalcon\Http;

use Phalcon\Bootstrap\Application;
use Phalcon\Bootstrap\MiddlewareStack;
use Phalcon\Debug\ExceptionHandler;
use Phalcon\Debug\FatalThrowableError;
use Phalcon\Mvc\ControllerInterface;
use \Phalcon\Mvc\DispatcherInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class Kernel implements KernelInterface
{

	/**
	 * @var Application
	 */
	protected  $app;

	protected $bootstrappers = [];

	protected $middleware = [];

	protected $middlewareGroups = [];

	protected $routeMiddleware = [];

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
		return (new MiddlewareStack())->setPassable($request)
					->setStack($this->middleware)
					->run(function ($request) {
						return $this->dispatchToRouter($request);
					});
	}

	/**
	 * @param FormRequest $request
	 * @return mixed|string|ResponseInterface
	 * @throws \Exception
	 */
	protected function dispatchToRouter(FormRequest $request)
	{
		$matchedRoute = $this->getMatchedRoute($request);

		if  ($matchedRoute instanceof Route && $matchedRoute->getMatch() !== null) {
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

		// Calling beforeHandleRequest
		$this->fireBeforeHandleRequestEvent($dispatcher);

		$this->setupDispatcher($dispatcher, $router);
		$controller  = $this->app->getShared($dispatcher->getControllerClass());

		if (!is_object($controller)) {
			throw new \Exception('Handler class not exists or can not be resolved by dependency manager');
		}

		$routeMiddleware = $this->gatherRouteMiddleware($matchedRoute);
		$controllerMiddleware = $this->gatherControllerMiddleware($controller, $dispatcher->getActiveMethod());

		$middleware = collect($this->mergeMiddleware($routeMiddleware, $controllerMiddleware))->map(function ($name) {
			return (array) $this->resolveMiddlewareName($name);
		})->all();

		return (new MiddlewareStack())->setPassable($request)
			->setStack($middleware)
			->run(function ($request) use ($dispatcher, $view){
				$controller = $dispatcher->dispatch();
				$possibleResponse = $dispatcher->getReturnedValue();

				$renderView = $possibleResponse !== false
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

			});
	}


	/**
	 * @param FormRequest $request
	 * @return Route|null
	 */
	protected function getMatchedRoute(FormRequest $request)
	{
		router()->handle();
		return router()->getMatchedRoute();
	}

	protected function callMatchedRouteHandler(Route $route)
	{
		$match = $route->getMatch();

		if ($match instanceof \Closure) {
			$match = \Closure::bind($match, $this->app);
		}

		return call_user_func_array($match, router()->getParams());
	}

	protected function gatherRouteMiddleware(Route $route)
	{
		return array_get($route->getPaths(), "middleware", []);
	}

	protected function resolveMiddlewareName($name)
	{
		if ($name instanceof \Closure) {
			return $name;
		}

		if (isset($this->routeMiddleware[$name])) {

			if ($this->routeMiddleware[$name] instanceof \Closure) {
				return $this->routeMiddleware[$name];
			}

			if (is_array($this->routeMiddleware[$name])) {
				return $this->parseMiddlewareGroup($this->routeMiddleware[$name]);
			}

		}

//		if (isset($this->routeMiddleware[$name]) && $this->routeMiddleware[$name] instanceof \Closure) {
//			return $this->routeMiddleware[$name];
//		}
//
//		if (isset($this->middlewareGroups[$name])) {
//			return $this->parseMiddlewareGroup($name);
//		}

		[$name, $parameters] = array_pad(explode(':', $name, 2), 2, null);
		return ($map[$name] ?? $name).(! is_null($parameters) ? ':' . $parameters : '');
	}

	protected function parseMiddlewareGroup(array $group)
	{
		$results = [];

		foreach ($group as $middleware) {
			$resolved = $this->resolveMiddlewareName($middleware);

			if (is_array($resolved)) {
				$results = array_merge($results, $resolved);
				continue;
			}

			$results[] = $this->resolveMiddlewareName($middleware);
		}

		return $results;
	}

	protected function gatherControllerMiddleware(ControllerInterface $controller, $method)
	{
		if (! method_exists($controller, 'getMiddleware')) {
			return [];
		}

		return collect($controller->getMiddleware())->reject(function ($data) use ($method) {
			$options = $data['options'];
			return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
				(! empty($options['except']) && in_array($method, (array) $options['except']));
		})->pluck('middleware')->all();
	}

	protected function mergeMiddleware()
	{
		$result = [];

		foreach (func_get_args() as $item) {
			$item = array_wrap($item);
			$result = array_merge($result, $item);
		}

		return array_unique($result, SORT_REGULAR);
	}

	protected function setupDispatcher(DispatcherInterface $dispatcher, RouterInterface $router)
	{
		$dispatcher->setModuleName($router->getModuleName());
		$dispatcher->setNamespaceName($router->getNamespaceName());
		$dispatcher->setControllerName($router->getControllerName());
		$dispatcher->setActionName($router->getActionName());
		$dispatcher->setParams($router->getParams());
	}

	/**
	 * @param FormRequest $request
	 * @param $response
	 * @return JsonResponse|Response|string
	 */
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