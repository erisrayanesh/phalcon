<?php

namespace Phalcon\Mvc\Model;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class ModelManagerServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('modelsManager', function() use ($di) {
			$manager = new Manager();
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}

}