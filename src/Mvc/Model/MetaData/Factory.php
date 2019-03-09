<?php

namespace Phalcon\Mvc\Model\MetaData;

use \Phalcon\Factory as BaseFactory;

class Factory extends BaseFactory
{
	public static function load($config)
	{
		return static::loadClass("Phalcon\\Mvc\Model\\MetaData\\\Adapter", $config);
	}


}