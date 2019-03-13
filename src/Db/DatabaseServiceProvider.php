<?php

namespace Phalcon\Db;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Db\Manager as DatabaseManager;

class DatabaseServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('db', function () use ($di) {
			$manager = new DatabaseManager();
			$manager->setEventsManager($di->getEventsManager());
			return $manager;
		});
	}

}