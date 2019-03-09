<?php

namespace Phalcon\Db;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Db\Manager as DatabaseManager;

class DatabaseServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('db', function () {
			return new DatabaseManager();
		});
	}

}