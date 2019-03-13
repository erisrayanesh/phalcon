<?php

namespace Phalcon\Db;

use Phalcon\Db\Adapter\Pdo\Factory;
use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{
	protected $driverType = AdapterInterface::class;

	protected $factory = Factory::class;

}