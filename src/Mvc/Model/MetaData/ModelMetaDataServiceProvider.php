<?php

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\MetaData\Files;
use Phalcon\Mvc\Model\MetaData\Memory;

class ModelMetaDataServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$di->setShared('modelsMetadata', function () {
			$config = $this->getConfig();

			if ($config->application->debug){
				return new Memory();
			} else {
				return new Files([
					'metaDataDir' => $config->application->cacheDir . 'metaData/'
				]);
			}
		});
	}

}