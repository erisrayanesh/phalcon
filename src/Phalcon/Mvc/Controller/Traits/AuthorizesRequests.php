<?php

namespace Phalcon\Mvc\Controller\Traits;


use Phalcon\Auth\AuthorizationException;

trait AuthorizesRequests
{

	protected function authorize($ability, $arguments = [])
	{
		return auth()->guard()->authorize($ability, $arguments);
	}


	protected function buildAuthorizationFailedResponse($name)
	{

	}

}