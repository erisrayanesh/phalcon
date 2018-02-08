<?php
namespace Phalcon\Translate;

use Phalcon\Config;
use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Mvc\User\Component;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\NativeArrayLoader\NativeArrayLoaderInterface;

class Locale extends Component
{

	/**
	 * @var array
	 */
	protected $adapters;

	protected $languages;

	protected $defaultLocale = "en_US";

	protected $locale = "en_US";

	protected $cookieVar = 'app-locale';

	/**
	 * @var NativeArray
	 */
	protected $cache;

	/**
	 * Locale constructor.
	 * @param \Phalcon\DiInterface $dependencyInjector
	 */
	public function __construct(\Phalcon\DiInterface $dependencyInjector)
	{
		$this->setDI($dependencyInjector);
	}

	public function init()
	{
		$cookieLocale = null;
		if ($this->cookies->has($this->getCookieVar())) {
			$cookieLocale = $this->cookies->get($this->getCookieVar())->getValue();
		}

		if (!$this->languageExists($cookieLocale)){
			$cookieLocale = null;
		}

		$this->setLocale($cookieLocale ?: $this->defaultLocale);
	}

	public function __call($name, $arguments)
	{
		if (method_exists($this->cache, $name)){
			return call_user_func_array([$this->cache, $name], $arguments);
		}
	}

	public function languageExists($language)
	{
		return array_key_exists($language, $this->languages);
	}



	/**
	 * @return array
	 */
	public function getAdapters()
	{
		return $this->adapters;
	}

	/**
	 * @param array $adapters
	 * @return Locale
	 */
	public function addAdapter($adapter)
	{
		$this->adapters[] = $adapter;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languages;
	}

	/**
	 * @param array $languages
	 * @return Locale
	 */
	public function setLanguages($languages)
	{
		$this->languages = $languages;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCookieVar()
	{
		return $this->cookieVar;
	}

	/**
	 * @param string $cookieVar
	 */
	public function setCookieVar($cookieVar)
	{
		$this->cookieVar = $cookieVar;
	}

	/**
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * @param string $locale
	 */
	public function setLocale($locale)
	{

		if (empty($locale) || !$this->languageExists($locale)) {

			//If locale not found and the cache was not empty then
			//there is no need to reload default locale
			if (empty($this->cache)){
				return;
			}

			$locale = $this->request->getBestLanguage();
		}

		if (!$this->languageExists($locale)){
			return;
		}

		$this->locale = $locale;
		$this->cookies->set($this->getCookieVar(), $locale);
		$this->cache($locale);

	}

	protected function cache($language)
	{
		$this->cache = new NativeArray(
			[
				'content' => $this->loadApaters($language),
			]
		);
	}

	protected function loadAdapters($language)
	{
		$content = [];
		foreach ($this->getAdapters() as $adapter){
			if ($adapter instanceof NativeArrayLoaderInterface){
				$content = array_merge($content, $adapter->load($language));
			}
		}
	}



}