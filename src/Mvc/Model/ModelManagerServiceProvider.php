<?php

namespace Phalcon\Mvc\Model;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class ModelManagerServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('modelsManager', function(){
			$manager = new Manager();
			$manager->setEventsManager($this->getEventsManager());
			return $manager;
		});
	}

}