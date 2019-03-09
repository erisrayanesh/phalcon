<?php

namespace Phalcon\Db;

use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{

	protected $default = "mysql";

	protected $driverType = AdapterInterface::class;


}