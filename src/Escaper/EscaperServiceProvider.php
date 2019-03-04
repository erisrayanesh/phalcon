<?php

namespace Phalcon\Escaper;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Escaper;

class EscaperServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->set('escaper', function () {
			return new Escaper();
		});
	}

}