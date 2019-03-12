<?php

namespace Phalcon\Translate\Loader;

use Phalcon\Translate\Exception;
use Phalcon\Translate\Loader;

class File extends Loader
{

	protected $baseDir;

	protected $fileExtension = "php";

	public function __construct($options = [])
	{
		if (!isset($options['dir'])){
			throw new Exception('Language base directory not specified');
		}

		$this->setBaseDir($options['dir']);
		$this->setFileExtension($options['extension'] ?? 'php');
	}

	public function load($language, $group)
	{
		$dir = $this->getBaseDir() . DIRECTORY_SEPARATOR . $language;
		$baseFile = $dir . DIRECTORY_SEPARATOR . $group . ".{$this->getFileExtension()}";

		if (!file_exists($baseFile)){
			return [];
		}

		return $this->convertToArray(require $baseFile);
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

}