<?php

namespace Phalcon\Debug;

use Exception;
use Phalcon\Auth\AuthenticationException;
use Phalcon\Auth\AuthorizationException;
use Phalcon\Http\JsonResponse;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\ModelNotFoundException;
use Phalcon\Validation\ValidationException;
use \Phalcon\Http\Response\Exception as HttpResponseException;
use Whoops\Run as Whoops;

Class Handler implements ExceptionHandler
{

	protected $errorsViewDir = "errors";

	protected $noReport = [
		//
	];

	protected $noFlash = [
		'password',
		'password_confirmation',
	];

	public function report(Exception $e)
	{

	}

	public function render(Exception $e)
	{
		if ($e instanceof \Phalcon\Mvc\Dispatcher\Exception) {
			switch ($e->getCode()) {
				case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
				case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
					$e = new HttpResponseException('', 404);
					break;
				default:
					$e = new HttpResponseException('', 500);
			}
		}

		if ($e instanceof ModelNotFoundException) {
			$e = new HttpResponseException($e->getMessage(), 404);
		} elseif ($e instanceof AuthorizationException) {
			$e = new HttpResponseException($e->getMessage(), 403);
		} //elseif ($e instanceof TokenMismatchException) {
			//$e = new HttpException(419, $e->getMessage(), $e);
		//}

		if ($e instanceof HttpResponseException){
			return $this->buildHttpResponseException($e);
		} elseif  ($e instanceof AuthenticationException){
			return $this->unauthenticated($e);
		} elseif  ($e instanceof ValidationException){
			return $this->convertValidationExceptionToResponse($e);
		}

		return $this->renderUnhandledException($e);
	}

	/**
	 * Convert an authentication exception into a response.
	 *
	 * @param  AuthenticationException  $exception
	 * @return Response
	 */
	protected function unauthenticated(AuthenticationException $exception)
	{
		return request_expects_json() ?
			tap(new Response('', 401), function (Response $response) use ($exception) {
			})
			: ($exception->redirectTo()? redirect($exception->redirectTo()) : redirect_route('login'));
	}

	/**
	 * Create a response object from the given validation exception.
	 *
	 * @param  ValidationException  $e
	 * @return Response
	 */
	protected function convertValidationExceptionToResponse(ValidationException $e)
	{
		if ($e->response) {
			return $e->response;
		}

		return request_expects_json()
			? $this->ValidationToJsonResponse($e)
			: $this->ValidationToResponse($e);
	}

	/**
	 * Convert a validation exception into a response.
	 *
	 * @param  ValidationException  $e
	 * @return Response
	 */
	protected function ValidationToResponse(ValidationException $e)
	{
		return redirect($e->redirectTo ?? previous_request_url(), 'errors', $e->errors());
	}

	/**
	 * Convert a validation exception into a JSON response.
	 *
	 * @param  ValidationException  $e
	 * @return Response
	 */
	protected function ValidationToJsonResponse(ValidationException $e)
	{
		return tap(new Response('', $e->status), function (Response $response) use ($e) {
			$response->setJsonContent([
				'message' => $e->getMessage(),
				'errors' => $e->errors(),
			]);
		});
	}

	/**
	 * Convert a HttpResponse exception into a response.
	 * @param HttpResponseException $e
	 * @return Response
	 */
	protected function buildHttpResponseException(HttpResponseException $e)
	{
		if (request_expects_json()){
			$response = new Response();
			$response->setStatusCode($e->getCode())
				->setJsonContent(["error" => $response->getHeaders()->get('Status'), "message" => $e->getMessage()]);
			return $response;
		}

		response()->setStatusCode($e->getCode());
		view($this->errorsViewDir . "/" . $e->getCode(), ["exception" => $e]);
	}

	/**
	 * Prepares a response for the given exception.
	 * @param Exception $e
	 * @return Response
	 */
	protected function renderUnhandledException(\Exception $e)
	{
		return request_expects_json()?
					$this->prepareJsonResponse($e) :
					$this->prepareResponse($e);
	}

	protected function prepareResponse(\Exception $e)
	{
		return $this->isDebugMode()?
			$this->renderExceptionWithWhoops($e) :
			$this->renderExceptionForDeployment($e);
	}

	protected function prepareJsonResponse(\Exception $e)
	{
		return $this->isDebugMode()?
					$this->renderExceptionWithWhoops($e, true) :
					$this->renderExceptionForDeployment($e, true);

	}

	protected function renderExceptionWithWhoops(\Exception $e, $json = false)
	{
		$whoops = new Whoops();

		if ($json){
			$handler = new \Whoops\Handler\JsonResponseHandler();
			$handler->addTraceToOutput(true);
		} else {
			$handler = new \Whoops\Handler\PrettyPageHandler();
			$handler->handleUnconditionally(true);
		}

		$whoops->pushHandler($handler);
		$whoops->writeToOutput(false);
		$whoops->allowQuit(false);

		return $json ?
			new JsonResponse($whoops->handleException($e), $e->getMessage()) :
			new Response($whoops->handleException($e), $e->getMessage());
	}

	protected function renderExceptionForDeployment(\Exception $e, $json = false)
	{
		$message = "Whoops! Something went wrong";
		return $json ?
			new Response($message, 500) :
			new JsonResponse (['message' =>$message],500,null,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Indicates debug mode
	 * @return bool
	 */
	protected function isDebugMode()
	{
		return true;
	}
}