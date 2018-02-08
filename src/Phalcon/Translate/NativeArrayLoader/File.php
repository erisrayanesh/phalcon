<?php
/**
 * Created by PhpStorm.
 * User: eris2
 * Date: 2/8/18
 * Time: 1:28 PM
 */

namespace Phalcon\Translate\NativeArrayLoader;


class File extends Loader
{

	protected $baseDir;

	protected $cacheDir;

	public function load($language)
	{
		return $this->readCacheFile($language);
	}

	/**
	 * @return mixed
	 */
	public function getBaseDir()
	{
		return $this->baseDir;
	}

	/**
	 * @param mixed $baseDir
	 * @return File
	 */
	public function setBaseDir($baseDir)
	{
		$this->baseDir = $baseDir;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCacheDir()
	{
		return $this->cacheDir;
	}

	/**
	 * @param mixed $cacheDir
	 * @return File
	 */
	public function setCacheDir($cacheDir)
	{
		$this->cacheDir = $cacheDir;

		return $this;
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

	protected function cacheToFile($language, $data)
	{
		return file_put_contents($this->getCacheFilePath($language), json_encode($data));
	}

	protected function isCacheFileAvailable($language)
	{
		$cache = $this->getCacheFilePath($language);
		$dir = $this->baseDir . $language;

		if (!file_exists($cache)){
			return false;
		}

		return filemtime($cache) >= filemtime($dir);
	}

	protected function getDefinitions($language)
	{
		$dir = $this->baseDir . DIRECTORY_SEPARATOR . $language;
		$baseFile = $dir . DIRECTORY_SEPARATOR . $language . ".php";

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
		return json_decode(file_get_contents($this->getCacheFilePath($language)), true);
	}

	protected function getCacheFilePath($language)
	{
		return $this->cacheDir . DIRECTORY_SEPARATOR . $language . ".php";
	}

}