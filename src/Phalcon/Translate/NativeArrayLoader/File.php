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

	protected $cache = true;

	protected $fileExtension = "php";

	protected $contentType = "array";

	public function load($language)
	{

		if (!$this->isCache()){
			return array_dot($this->getDefinitions($language));
		}

		$definitions = [];

		if ($this->isCacheFileAvailable($language)){
			$definitions = $this->getDefinitionsFromCache($language);
		}

		if (empty($definitions)){
			// get original definitions
			$definitions = array_dot($this->getDefinitions($language));
			// cache the original definitions
			$this->cacheToFile($language, $definitions);
		}

		return $definitions;
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

	/**
	 * @return bool
	 */
	public function isCache()
	{
		return $this->cache;
	}

	/**
	 * @param bool $cache
	 * @return File
	 */
	public function setCache($cache)
	{
		$this->cache = (bool) $cache;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileExtension()
	{
		return $this->fileExtension;
	}

	/**
	 * @param string $fileExtension
	 * @return File
	 */
	public function setFileExtension($fileExtension)
	{
		$this->fileExtension = $fileExtension;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * @param string $contentType
	 * @return File
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
		return $this;
	}


	protected function cacheToFile($language, $data)
	{
		return file_put_contents($this->getCacheFilePath($language), json_encode($data));
	}

	protected function isCacheFileAvailable($language)
	{
		$cache = $this->getCacheFilePath($language);
		$dir = $this->getBaseDir() . $language;

		if (!file_exists($cache)){
			return false;
		}

		return filemtime($cache) >= filemtime($dir);
	}

	protected function getDefinitions($language)
	{
		$dir = $this->getBaseDir() . DIRECTORY_SEPARATOR . $language;
		$baseFile = $dir . DIRECTORY_SEPARATOR . $language . ".{$this->getFileExtension()}";

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
			if($fileinfo->isDot() || !$fileinfo->isFile() || $fileinfo->getExtension() !== $this->getFileExtension()) continue;

			$fileData = include $fileinfo->getPathname();

			if ($this->getContentType() == "array"){
				if ($fileData instanceof Arrayable){
					$fileData = $fileData->toArray();
				}
			}

			if ($this->getContentType() == "json" and is_string($fileData)){
				$fileData = json_decode($fileData, true);
			}

			if (is_array($fileData)){
				$translations[strtoupper($fileinfo->getBasename(".{$this->getFileExtension()}"))] = $fileData;
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
		return $this->cacheDir . DIRECTORY_SEPARATOR . $language . ".json";
	}

}