<?php

namespace Phalcon\Cache;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class CacheServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('cache', function () use ($di){
			$manager = new Manager();
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}

}