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
		$di->setShared('router', function () use ($di) {
			$router = new Router(false);
			$router->setEventsManager($di->getEvenetsManager());
			return $router;
		});
	}

}