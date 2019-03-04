<?php

namespace Phalcon\Filter;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Filter;

class FilterServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->set('filter', function () {
			return new Filter();
		});
	}

}