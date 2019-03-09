<?php

namespace Phalcon\FileSystem;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

class FileSystemServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$this->registerNativeFileSystem($di);

		$di->setShared('filesystem', function () use ($di) {
			$fileSystem = new Manager($this);
			$fileSystem->setEventsManager($this->getEventsManager());
			return $fileSystem;
		});

		$di->setShared('files', function () {
			return new FileSystem();
		});
	}



}