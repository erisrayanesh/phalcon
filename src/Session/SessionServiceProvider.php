<?php

namespace Phalcon\Session;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class SessionServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('session', function () use ($di) {
			$manager = new Manager();
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}

}