<?php

namespace Phalcon\Mvc\View;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\View;

class ViewServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('view', function () use ($di) {
			$view = new View();
			$view->setEventsManager($di->getEventsManager());
			return $view;
		});
	}

}