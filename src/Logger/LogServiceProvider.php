<?php

namespace Phalcon\Logger\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Logger\Manager as LogManager;

class LogServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('logger', function (){
			return new LogManager();
		});
	}

}