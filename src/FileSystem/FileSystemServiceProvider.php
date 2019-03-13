<?php

namespace Phalcon\FileSystem;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class FileSystemServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('storage', function () use ($di) {
			$fileSystem = new Manager();
			$fileSystem->setEventsManager($di->getEventsManager());
			return $fileSystem;
		});

		$di->setShared('files', function () {
			return new FileSystem();
		});
	}



}