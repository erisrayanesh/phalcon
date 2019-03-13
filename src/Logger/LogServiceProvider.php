<?php

namespace Phalcon\Logger;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class LogServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('logger', function (){
			return new Manager();
		});
	}

}