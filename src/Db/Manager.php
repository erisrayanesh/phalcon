<?php

namespace Phalcon\Db;

use Phalcon\Db\Adapter\Pdo\Factory;
use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Support\ProvidesAdapter;

class Manager extends BaseManager
{

	use ProvidesAdapter;

	protected $driverType = AdapterInterface::class;

	protected $factory = Factory::class;

}