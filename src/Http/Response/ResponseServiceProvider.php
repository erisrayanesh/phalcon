<?php

namespace Phalcon\Http\Response;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class ResponseServiceProvider implements ServiceProviderInterface
{
	public function register(DiInterface $di)
	{
		$di->setShared('response', function () {
			return new Factory();
		});
	}
}