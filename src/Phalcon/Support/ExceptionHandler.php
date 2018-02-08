<?php

namespace Phalcon\Support;

use Phalcon\Mvc\User\Component;
use Phalcon\Validation\Exceptions\ValidationException;

class ExceptionHandler extends Component
{

	public function __construct(\Phalcon\DiInterface $dependencyInjector)
	{
		$this->setDI($dependencyInjector);
	}


	public function render(\Exception $e)
	{

		if ($e instanceof ValidationException){
			return $e->getResponse();
		}

		throw $e;

	}
}