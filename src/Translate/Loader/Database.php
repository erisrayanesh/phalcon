<?php

namespace Phalcon\Translate\Loader;

use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Translate\Loader;

class Database extends Loader
{

	protected $languageModel;
	protected $translationsModel;
	protected $keysModel;

	public function load($language)
	{

//		$query = "SELECT `translation_keys`.`title`, `translations`.`value` FROM `translations` " .
//				 "INNER JOIN `translation_keys` ON `translations`.`translation_key_id` = `translation_keys`.`id` " .
//				 "INNER JOIN `languages` ON `translations`.`language_id` = `languages`.`id` " .
//				 "WHERE `languages`.`code` = :code:";

		$query = "SELECT " . $this->getKeysModel() . ".title, " . $this->getTranslationsModel() . ".value FROM " . $this->getTranslationsModel() . " " .
			"INNER JOIN " . $this->getKeysModel() . " ON " . $this->getTranslationsModel() . ".translation_key_id = " . $this->getKeysModel() . ".id " .
			"INNER JOIN " . $this->getLanguageModel() . " ON " . $this->getTranslationsModel() . ".language_id = " . $this->getLanguageModel() . ".id " .
			"WHERE " . $this->getLanguageModel() . ".code = :code:";

		$builder = $this->newBuilder($query);

		$results = $builder->execute([
			"code" => $language
		]);

		return $this->postProcessResults($results);
	}

	/**
	 * @return mixed
	 */
	public function getLanguageModel()
	{
		return $this->languageModel;
	}

	/**
	 * @param mixed $languageModel
	 * @return Database
	 */
	public function setLanguageModel($languageModel)
	{
		$this->languageModel = $languageModel;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTranslationsModel()
	{
		return $this->translationsModel;
	}

	/**
	 * @param mixed $translationsModel
	 * @return Database
	 */
	public function setTranslationsModel($translationsModel)
	{
		$this->translationsModel = $translationsModel;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getKeysModel()
	{
		return $this->keysModel;
	}

	/**
	 * @param mixed $keysModel
	 * @return Database
	 */
	public function setKeysModel($keysModel)
	{
		$this->keysModel = $keysModel;

		return $this;
	}


	/**
	 * @return Query
	 */
	protected function newBuilder($query)
	{
		return $this->di->get('modelsManager')->createQuery($query);
	}

	protected function postProcessResults($results)
	{
		return array_pluck($results->toArray(), 'value', 'title');
	}

}