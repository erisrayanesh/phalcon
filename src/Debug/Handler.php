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

	protected $ignoreReporting = [
		//
	];

	protected $ignoreFlashing = [
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
		return request()->expectsJson() ?
			response()->json(['message' => $exception->getMessage()], 401)
			: response()->redirectTo($exception->redirectTo() ??  route('login'));
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

		return request()->expectsJson()
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
		return redirect($e->redirectTo ?? previous_request_url())
				->withInput(request()->except($this->ignoreFlashing))
				->withMessage($e->errors());
	}

	/**
	 * Convert a validation exception into a JSON response.
	 *
	 * @param  ValidationException  $e
	 * @return Response
	 */
	protected function ValidationToJsonResponse(ValidationException $e)
	{
		return response()->json([
			'message' => $e->getMessage(),
			'errors' => $e->errors(),
		], $e->status);
	}

	/**
	 * Convert a HttpResponse exception into a response.
	 * @param HttpResponseException $e
	 * @return Response
	 */
	protected function buildHttpResponseException(HttpResponseException $e)
	{
		if (request()->expectsJson()){
			return response()->json(["message" => $e->getMessage()], $e->getCode());
		}

		return response()->view($this->errorsViewDir . "/" . $e->getCode(), ["exception" => $e], $e->getCode());
	}

	/**
	 * Prepares a response for the given exception.
	 * @param Exception $e
	 * @return Response
	 */
	protected function renderUnhandledException(\Exception $e)
	{
		return request()->expectsJson()?
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
			response()->json($whoops->handleException($e), 500) :
			response($whoops->handleException($e), 500);
	}

	protected function renderExceptionForDeployment(\Exception $e, $json = false)
	{
		$message = "Whoops! Something went wrong";
		return $json ?
			response($message, 500) :
			response()->json(['message' => $message],500,null,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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