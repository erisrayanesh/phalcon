<?php

namespace Phalcon\Http\Response;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Http\ResponseFactory;

class ResponseServiceProvider implements ServiceProviderInterface
{
	public function register(DiInterface $di)
	{
		$di->setShared('response', function () {
			return new ResponseFactory();
		});
	}
}