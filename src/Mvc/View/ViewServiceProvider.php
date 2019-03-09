<?php

namespace Phalcon\Mvc\View;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\View;

class ViewServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('view', function () {
			$view = new View();
			$view->setEventsManager($this->getEventsManager());
			return $view;
		});
	}

}