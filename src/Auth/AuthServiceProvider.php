<?php

namespace Phalcon\Auth;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Auth\Manager as AuthManager;

class AuthServiceProvider implements ServiceProviderInterface
{

	/**
	 * @var DiInterface
	 */
	protected $di;

	public function register(DiInterface $di)
	{
		$di->setShared('auth', function () use ($di) {
			$auth = new AuthManager();
			$auth->setEventsManager($this->getEventsManager());
			return $auth;
		});
	}

}