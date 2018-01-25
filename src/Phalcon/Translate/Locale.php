<?php
namespace Phalcon\Translate;

use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Mvc\User\Component;
use Phalcon\Translate\Adapter\NativeArray;

class Locale extends Component
{
	protected $baseDir = "";

	protected $languages = [];
	protected $defaultLocale;

	protected $cookieVar = 'app-locale';
	protected $locale;

	/**
	 * @var NativeArray
	 */
	protected $cache;

	protected $cacheDir;

	/**
	 * Locale constructor.
	 * @param string $baseDir Directory where the language files exist
	 * @param array $languages List of available languages
	 * @param string $locale Current user language
	 * @param string $defaultLocale Alternative language
	 */
	public function __construct($baseDir, $cacheDir, $languages = [], $defaultLocale = null)
	{
		$this->baseDir = $baseDir;
		$this->cacheDir = $cacheDir;
		$this->languages = $languages;
		$this->defaultLocale = $defaultLocale;
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

	public function getTranslator($language)
	{
		$dir = $this->baseDir .  $language;

		if (!file_exists($dir)){
			throw new \Exception("Language directory '$language' not found in '{$this->baseDir}'");
		}

		return new NativeArray(
			[
				'content' => $this->readCacheFile($language),
			]
		);
	}

	public function languageExists($language)
	{
		return array_key_exists($language, $this->languages);
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
		$this->cacheToMemory($locale);

	}

	protected function cacheToMemory($language)
	{
		return $this->cache = $this->getTranslator($language);
	}

	protected function cacheToFile($language, $data)
	{
		return file_put_contents($this->cacheDir . "lang" . DS . $language . ".php", json_encode($data));
	}

	protected function isCacheFileAvailable($language)
	{
		$cache = $this->cacheDir . "lang" . DS . $language . ".php";
		$dir = $this->baseDir . $language;

		if (!file_exists($cache)){
			return false;
		}

		return filemtime($cache) >= filemtime($dir);
	}

	protected function readCacheFile($language)
	{
		$definitions = [];

		if (!$this->isCacheFileAvailable($language)){
			// get original definitions
			$definitions = array_dot($this->getDefinitions($language));
			// cache the original definitions
			$this->cacheToFile($language, $definitions);
		}

		if (empty($definitions)){
			$definitions = $this->getDefinitionsFromCache($language);
		}

		return $definitions;
	}

	protected function getDefinitions($language)
	{
		$dir = $this->baseDir . DS . $language;
		$baseFile = $dir . DS . $language . ".php";

		if (file_exists($baseFile)){
			$translations = require $baseFile;
		}

		$files = new \DirectoryIterator($dir);

		if (!$files){
			throw new \Exception("getFileList: Failed opening directory $dir for reading");
		}

		foreach($files as $fileinfo) {

			//$fileinfo instanceof \SplFileInfo;

			// skip hidden files
			if($fileinfo->isDot() || !$fileinfo->isFile() || $fileinfo->getExtension() !== 'php') continue;

			$fileData = include $fileinfo->getPathname();

			if ($fileData instanceof Arrayable){
				$fileData = $fileData->toArray();
			}

			if (is_array($fileData)){
				$translations[strtoupper($fileinfo->getBasename('.php'))] = $fileData;
			}
		}

		return $translations;
	}

	protected function getDefinitionsFromCache($language)
	{
		return json_decode(file_get_contents($this->cacheDir . "lang" . DS . $language . ".php"), true);
	}

}