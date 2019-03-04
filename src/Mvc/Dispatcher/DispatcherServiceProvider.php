<?php

namespace Phalcon\Mvc\Dispatcher;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;

class DispatcherServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('dispatcher', function () {
			$dispatcher = new Dispatcher();
			$dispatcher->setDefaultNamespace('Apps\Http\Controllers');
			$dispatcher->setEventsManager($this->getEventsManager());
			return $dispatcher;
		});
	}

}