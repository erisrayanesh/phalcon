<?php

namespace Phalcon\Events;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class EventsServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('eventsManager', function (){
			return new Manager();
		});
	}

}