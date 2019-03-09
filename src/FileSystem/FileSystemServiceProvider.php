<?php

namespace Phalcon\FileSystem;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class FileSystemServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('filesystem', function () {
			$fileSystem = new Manager();
			$fileSystem->setEventsManager($this->getEventsManager());
			return $fileSystem;
		});

		$di->setShared('files', function () {
			return new FileSystem();
		});
	}



}