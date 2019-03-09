<?php

namespace Phalcon\Flash;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Flash\Session as FlashSession;

class FlashSessionServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('flashSession', function () {
			return new FlashSession();
		});

		$di->setShared('flashInputs', function () {
			$inputs = new FlashInputs();
			$inputs->setEventsManager($this->getEventsManager());
			return $inputs;
		});
	}

}