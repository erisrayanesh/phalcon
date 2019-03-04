<?php

namespace Phalcon\Security;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Security;

class SecurityServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('security', function () {
			$security = new Security();

			// Set the password hashing factor to 12 rounds
			$security->setWorkFactor(12);

			return $security;
		});
	}

}