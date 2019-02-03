<?php

namespace Phalcon\Support;

use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Validation\Exceptions\ValidationException;
use \Phalcon\Http\Response\Exception as HttpResponseException;
use Whoops\Run as Whoops;

trait ExceptionHandler
{

	protected $errorsViewDir = "errors";

	public function handle(Dispatcher $dispatcher, \Exception $e)
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

		//TODO: throwing the exception causes cyclic routing.
		// The dispatcher tries to redispatch the exception handler controller to handle the exception
		//throw $e;

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
		(config('debug')?
			$this->renderExceptionWithWhoops($exception) :
			$this->renderFriendlyError($exception))->send();
	}

	protected function renderExceptionWithWhoops(\Exception $e)
	{
		$whoops = new Whoops();
		if (request()->isAjax()){
			$handler = new \Whoops\Handler\JsonResponseHandler();
		} else {
			$handler = new \Whoops\Handler\PrettyPageHandler();
			$handler->handleUnconditionally(true);
			$handler->addDataTable('Application (Request)', array(
				'URI'         => request()->getScheme().'://' . request()->getServer('HTTP_HOST') . request()->getServer('REQUEST_URI'),
				'Request URI' => request()->getServer('REQUEST_URI'),
				'Path Info'   => request()->getServer('PATH_INFO'),
				'HTTP Method' => request()->getMethod(),
				'Script Name' => request()->getServer('SCRIPT_NAME'),
				//'Base Path'   => $request->getBasePath(),
				//'Base URL'    => $request->getBaseUrl(),
				'Scheme'      => request()->getScheme(),
				'Port'        => request()->getServer('SERVER_PORT'),
				'Host'        => request()->getServer('HTTP_HOST'),
			));
		}
		$whoops->pushHandler($handler);
		$whoops->writeToOutput(false);
		$whoops->allowQuit(false);
		return new Response($whoops->handleException($e), 500, []);
	}

	protected function renderFriendlyError(\Exception $e)
	{
		$response = new Response();
		$response->setStatusCode(500);
		if (request()->isAjax()){
			$response->setJsonContent(["code" => 500, "message" => $e->getMessage()]);
		} else {
			$response->setContent("Whoops! Something went wrong");
		}
		return $response;
	}
}