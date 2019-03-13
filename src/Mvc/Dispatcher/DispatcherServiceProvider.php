<?php

namespace Phalcon\Mvc\Dispatcher;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;

class DispatcherServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('dispatcher', function () use ($di) {
			$dispatcher = new Dispatcher();
			$dispatcher->setEventsManager($di->getEventsManager());
			return $dispatcher;
		});
	}

}