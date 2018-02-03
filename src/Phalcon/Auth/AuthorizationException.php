<?php

namespace Phalcon\Auth;

class AuthorizationException extends \Exception
{
	protected $action;

	/**
	 * AuthorizationException constructor.
	 * @param $action
	 */
	public function __construct($action = "", $message = "", $code = 0, Throwable $previous = null)
	{
		$this->action = $action;
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}




}