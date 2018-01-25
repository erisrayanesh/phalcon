<?php

namespace Phalcon\Mvc\Controller\Traits;


trait Authorizable
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