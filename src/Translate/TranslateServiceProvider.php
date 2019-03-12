<?php

namespace Phalcon\Translate;

use Apps\Languages;
use Apps\TranslationKeys;
use Apps\Translations;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Translate\Manager;
use Phalcon\Translate\NativeArrayLoader\Database;
use Phalcon\Translate\NativeArrayLoader\File;

class TranslateServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{

		$adapters = $this->getAdapters($di, $di->get('config')->localization->adapters);
		$di->setShared('locale', function () use ($di, $adapters) {
			$config = $this->getConfig();

			$locale = new Manager($this);

			$locale->setEventsManager($this->getEventsManager());

			$locale->setLanguages($config->localization->languages->toArray())
				->setCookieVar($config->localization->cookie);

			foreach ($adapters as $adapter){
				$locale->addAdapter($adapter);
			}

			$locale->init();

			return $locale;
		});
	}

	protected function getAdapters(DiInterface $di, $adaptersList)
	{
		$adapters = [];

		foreach ($adaptersList as $key => $config) {

			$method = "build" . camelize($key) . "Adapter";
			if (method_exists($this, $method)){
				$adapters[] = call_user_func_array([$this, $method], [$di, $config]);
			}

		}

		return $adapters;
	}

	protected function buildFileAdapter(DiInterface $di, $config)
	{
		$file = new File($di);
		return $file->setBaseDir($config->dir)
					->setCacheDir($config->cacheDir)
					->setCache($config->cache)
					->setContentType($config->type)
					->setFileExtension($config->ext);
	}

	protected function buildDbAdapter(DiInterface $di, $config)
	{
		$db = new Database($di);
		$db->setLanguageModel($config->models->languages)
			->setKeysModel($config->models->keys)
			->setTranslationsModel($config->models->translations);
		return $db;
	}

}