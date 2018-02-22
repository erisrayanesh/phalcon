<?php

namespace Phalcon\Support;

use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Component;
use Phalcon\Validation\Exceptions\ValidationException;
use \Phalcon\Http\Response\Exception as HttpResponseException;

class ExceptionHandler extends Component
{

	protected $errorsViewDir = "errors";

	public function __construct(\Phalcon\DiInterface $dependencyInjector)
	{
		$this->setDI($dependencyInjector);
	}


	public function render(Dispatcher $dispatcher, \Exception $e)
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


		if ($e instanceof HttpResponseException){
			return $this->buildHttpResponseException($e);
		}

		if ($e instanceof ValidationException){
			return $e->getResponse();
		}

		throw $e;

	}


	protected function buildHttpResponseException(HttpResponseException $exception)
	{
		if (request_expects_json()){
			$response = new Response();
			$response->setStatusCode($exception->getCode());
			$response->setJsonContent(["message" => $exception->getMessage()]);
			return $response;
		}

		response()->setStatusCode($exception->getCode());
		view($this->errorsViewDir . "/" . $exception->getCode(), ["exception" => $exception]);
		return response();
	}
}