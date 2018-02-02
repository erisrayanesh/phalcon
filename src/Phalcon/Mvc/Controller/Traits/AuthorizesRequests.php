<?php

namespace Phalcon\Mvc\Controller\Traits;


trait AuthorizesRequests
{

	protected function authorize($name)
	{
		$ret = true;

		if (!$ret){
			$ret = false;
			$this->onAuthorizationFailed($name);
		}

		return $ret;
	}


	protected function onAuthorizationFailed($name)
	{

	}

}