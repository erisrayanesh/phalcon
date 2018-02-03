<?php

namespace Phalcon\Mvc\Controller\Traits;


use Phalcon\Auth\AuthorizationException;

trait AuthorizesRequests
{

	protected function authorize($name)
	{
		$ret = false;
		if (!$ret){
			throw new AuthorizationException();
		}

	}


	protected function buildAuthorizationFailedResponse($name)
	{

	}

}