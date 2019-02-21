<?php

namespace Phalcon\Validation;

use Phalcon\Http\Response;
use Phalcon\Validation;

class ValidationException extends \Exception
{
	/**
	 * The validator instance.
	 *
	 * @var Validation
	 */
	public $validator;

	/**
	 * The recommended response to send to the client.
	 *
	 * @var Response
	 */
	public $response;

	/**
	 * Create a new exception instance.
	 *
	 * @param  Validation  $validator
	 * @param  Response  $response
	 * @return void
	 */
	public function __construct($validator, $response = null)
	{
		parent::__construct('Input validation failed');

		$this->response = $response;
		$this->validator = $validator;
	}

	/**
	 * Get the underlying response instance.
	 *
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}