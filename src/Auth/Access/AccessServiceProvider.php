<?php

namespace Phalcon\Auth\Access;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class AccessServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('access', function () use ($di) {
			$manager = new Manager(function ($guard = null) use ($di) {
				return call_user_func($di->get('auth')->guard($guard)->user());
			});
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}
}