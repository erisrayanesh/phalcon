<?php

namespace Phalcon\Debug;

use Exception;
use Phalcon\Http\Response;
use Phalcon\Validation\ValidationException;
use \Phalcon\Http\Response\Exception as HttpResponseException;
use Whoops\Run as Whoops;

Class Handler implements ExceptionHandler
{

	protected $errorsViewDir = "errors";

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

		if ($e instanceof ValidationException){
			return $e->getResponse();
		}

		if ($e instanceof HttpResponseException){
			return $this->buildHttpResponseException($e);
		}

		return $this->renderUnhandledException($e);
	}

	protected function buildHttpResponseException(HttpResponseException $exception)
	{
		if (request_expects_json()){
			$response = new Response();
			$response->setStatusCode($exception->getCode());
			$response->setJsonContent(["error" => $response->getHeaders()->get('Status'), "message" => $exception->getMessage()]);
			return $response;
		}

		response()->setStatusCode($exception->getCode());
		view($this->errorsViewDir . "/" . $exception->getCode(), ["exception" => $exception]);
	}

	protected function renderUnhandledException(\Exception $exception)
	{
		return $this->isDebugMode()?
					$this->renderExceptionWithWhoops($exception) :
					$this->renderFriendlyError($exception);
	}

	protected function renderExceptionWithWhoops(\Exception $e)
	{
		$whoops = new Whoops();

		if (request()->isAjax()){
			$handler = new \Whoops\Handler\JsonResponseHandler();
		} else {
			$handler = new \Whoops\Handler\PrettyPageHandler();
			$handler->handleUnconditionally(true);
		}

		$whoops->pushHandler($handler);
		$whoops->writeToOutput(false);
		$whoops->allowQuit(false);
		return new Response($whoops->handleException($e), 500);
	}

	protected function renderFriendlyError(\Exception $e)
	{
		$response = new Response('', 500);

		if (request()->isAjax()){
			$response->setJsonContent(["code" => 500, "message" => $e->getMessage()]);
		} else {
			$response->setContent("Whoops! Something went wrong");
		}

		return $response;
	}

	protected function isDebugMode()
	{
		return true;
	}
}