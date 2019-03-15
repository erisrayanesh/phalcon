<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Mvc\Model\MetaDataInterface;
use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{
	protected $driverType = MetaDataInterface::class;

	protected $namespace = __NAMESPACE__;

}