<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Session\Adapter\Files;
use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Support\ProvidesAdapter;

class Manager extends BaseManager
{

	use ProvidesAdapter;

	protected $driverType = StrategyInterface::class;

	protected $factory = Factory::class;

}