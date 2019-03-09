<?php

namespace Phalcon\Crypt;

use Phalcon\Crypt;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class CryptographyServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('crypt', function () {
			return new Crypt();
		});
	}

}