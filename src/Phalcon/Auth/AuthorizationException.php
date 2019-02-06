<?php

namespace Phalcon\Auth;

class AuthorizationException extends \Exception
{
	protected $ability;

	/**
	 * AuthorizationException constructor.
	 * @param $ability
	 * @param $message
	 * @param $code
	 * @param $previous
	 */
	public function __construct($ability = "", $message = "", $code = 0, \Throwable $previous = null)
	{
		parent::__construct();
		$this->ability = $ability;
	}

	/**
	 * @return string
	 */
	public function getAbility()
	{
		return $this->ability;
	}




}