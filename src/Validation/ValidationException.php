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
	 * The status code to use for the response.
	 *
	 * @var int
	 */
	public $status = 422;

	/**
	 * The path the client should be redirected to.
	 *
	 * @var string
	 */
	public $redirectTo;

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

	public function errors()
	{
		return $this->validator->getMessages();
	}

	public function status($status)
	{
		$this->status = $status;

		return $this;
	}

	public function redirectTo($url)
	{
		$this->redirectTo = $url;

		return $this;
	}
}