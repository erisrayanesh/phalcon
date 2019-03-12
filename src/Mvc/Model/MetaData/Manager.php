<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Support\BuildsAdapterByFactory;
use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Support\ProvidesAdapter;

class Manager extends BaseManager
{

	use ProvidesAdapter, BuildsAdapterByFactory;

	protected $driverType = StrategyInterface::class;

	protected $factory = Factory::class;

}