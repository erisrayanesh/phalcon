<?php

namespace Phalcon\Debug;

use Exception;
use Phalcon\Http\ResponseInterface;

interface ExceptionHandler
{
	/**
	 * Report or log an exception.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e);

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Exception  $e
	 * @return ResponseInterface
	 */
	public function render(Exception $e);

}