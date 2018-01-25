<?php

namespace Phalcon\Mvc\Controller\Traits;


trait ControllerAuthorization
{

	protected function authorize($name)
	{
		$ret = true;

		if (!$ret){
			$this->onAuthorizationFailed($name);
		}

		return true;
	}


	protected function onAuthorizationFailed($name)
	{

	}

}