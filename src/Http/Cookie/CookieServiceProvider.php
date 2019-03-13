<?php

namespace Phalcon\Http\Cookie;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class CookieServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('cookie', function () use ($di){
			return new Factory();
		});
	}

}