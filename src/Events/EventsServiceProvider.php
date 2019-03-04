<?php

namespace Phalcon\Events;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventManager;

class EventsServiceProvider implements ServiceProviderInterface
{

	protected $listeners = [];

	public function boot()
	{
		foreach ($this->listeners() as $event => $listeners) {
			foreach ($listeners as $listener) {
				DI()->getEventsManager()->attach($event, $this->resolveListener(DI(), $listener));
			}
		}
	}

	public function register(DiInterface $di)
	{
		$di->setShared('eventsManager', function (){
			return new EventManager();
		});

	}

	public function listeners()
	{
		return $this->listeners;
	}

	protected function resolveListener(DiInterface $di, $listener)
	{
		if (is_object($listener)){
			return $listener;
		}

		return new $listener($di);
	}
}