<?php

namespace Phalcon\Cache;

use Phalcon\Cache\Backend\Factory;
use Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{

	protected $driverType = BackendInterface::class;

	protected $factory = Factory::class;

}