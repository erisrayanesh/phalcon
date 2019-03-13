<?php

namespace Phalcon\Translate;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class TranslateServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('locale', function () use ($di) {
			$locale = new Manager();
			$locale->setEventsManager($this->getEventsManager());
			return $locale;
		});
	}
}