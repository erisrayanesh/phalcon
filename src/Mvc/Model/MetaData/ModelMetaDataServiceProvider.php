<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\MetaData\Files;
use Phalcon\Mvc\Model\MetaData\Memory;

class ModelMetaDataServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('modelsMetadata', function () use ($di) {
			$manager = new Manager();
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}

}