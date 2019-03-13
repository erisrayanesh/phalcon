<?php

namespace Phalcon\Mvc\Router;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Route;

class RouterServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('router', function () {
			return new Router(false);
		});
	}

}