<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Session\Adapter\Files;
use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{

	protected $default = "file";

	protected $driverType = StrategyInterface::class;

}