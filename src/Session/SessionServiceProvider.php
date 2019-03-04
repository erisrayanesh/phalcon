<?php

namespace Phalcon\Session;


use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Session\Manager as SessionManager;

class SessionServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('session', function () {
			return new SessionManager();
		});
	}

}