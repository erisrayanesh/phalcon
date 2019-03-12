<?php

namespace Phalcon\Db;

use Phalcon\Db\Adapter\Pdo\Factory;
use Phalcon\Support\BuildsAdapterByFactory;
use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Support\ProvidesAdapter;

class Manager extends BaseManager
{

	use ProvidesAdapter, BuildsAdapterByFactory;

	protected $driverType = AdapterInterface::class;

	protected $factory = Factory::class;

}