<?php
/**
 * Created by PhpStorm.
 * User: eris2
 * Date: 3/4/19
 * Time: 12:27 AM
 */

namespace Phalcon\Session;

use Phalcon\Session\Adapter\Files;
use \Phalcon\Support\Manager as BaseManager;

class Manager extends BaseManager
{

	protected $default = "file";

	protected $driverType = AdapterInterface::class;

	protected function createFileAdapter($driver, $config)
	{
		$adapter = new Files($config);

		if (isset($config['name'])){
			$adapter->setName($config['name']);
		}

		return $adapter;
	}
}