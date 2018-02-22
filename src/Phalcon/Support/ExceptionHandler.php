<?php

namespace Phalcon\Support;

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


	public function render(\Exception $e)
	{
		if ($e instanceof HttpResponseException){
			$this->buildHttpResponseException($e);
		}


		if ($e instanceof ValidationException){
			return $e->getResponse();
		}

		throw $e;

	}


	protected function buildHttpResponseException(HttpResponseException $exception)
	{
		response()->setStatusCode($exception->getCode());
		if (request_expects_json()){
			response()->setJsonContent(["message" => $exception->getMessage()]);
			return;
		}
		view($this->errorsViewDir . "/" . $exception->getCode(), ["exception" => $exception]);
	}
}