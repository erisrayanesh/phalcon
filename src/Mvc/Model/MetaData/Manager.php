<?php

namespace Phalcon\Mvc\Model\MetaData;

use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{
	protected $driverType = StrategyInterface::class;

	protected $namespace = __NAMESPACE__;

}