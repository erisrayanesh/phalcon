<?php

namespace Phalcon\Mvc\Model\Resultset;

use Phalcon\Cache\BackendInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;

class Advanced extends Resultset
{


	protected $with = null;

	protected $_model;

	protected $_columnMap;

	protected $_keepSnapshots = false;

	/**
	 * Phalcon\Mvc\Model\Resultset\Simple constructor
	 *
	 * @param array columnMap
	 * @param \Phalcon\Mvc\ModelInterface  model
	 * @param \Phalcon\Db\Result\Pdo|null result
	 * @param \Phalcon\Cache\BackendInterface cache
	 * @param boolean keepSnapshots
	 */
	public function __construct($columnMap, $model, $result, BackendInterface $cache = null, $keepSnapshots = null)
	{
		$this->_model = $model;
		$this->_columnMap = $columnMap;

		/**
		 * Set if the returned resultset must keep the record snapshots
		 */
		$this->_keepSnapshots = $keepSnapshots;

		parent::__construct($result, $cache);
	}

	/**
	 * Returns current row in the resultset
	 */
	public final function current()
	{

		$activeRow = $this->_activeRow;
		if ($activeRow !== null) {
			return $activeRow;
		}

		/**
		 * Current row is set by seek() operations
		 */
		$row = $this->_row;

		/**
		 * Valid records are arrays
		 */
		if (!is_array($row)) {
			$this->_activeRow = false;

			return false;
		}

		/**
		 * Get current hydration mode
		 */
		$hydrateMode = $this->_hydrateMode;

		/**
		 * Get the resultset column map
		 */
		$columnMap = $this->_columnMap;

		/**
		 * Hydrate based on the current hydration
		 */
		switch ($hydrateMode) {

			case Resultset::HYDRATE_RECORDS:

				/**
				 * Set records as dirty state PERSISTENT by default
				 * Performs the standard hydration based on objects
				 */
				if (ini_get("orm.late_state_binding")) {

					if ($this->_model instanceof \Phalcon\Mvc\Model) {
						$modelName = get_class($this->_model);
					} else {
						$modelName = "Phalcon\\Mvc\\Model";
					}

					$activeRow = $modelName::cloneResultMap($this->_model, $row, $columnMap, Model::DIRTY_STATE_PERSISTENT, $this->_keepSnapshots);
				} else {
					$activeRow = Model::cloneResultMap($this->_model, $row, $columnMap, Model::DIRTY_STATE_PERSISTENT, $this->_keepSnapshots);
				}
				break;

			default:
				/**
				 * Other kinds of hydrations
				 */
				$activeRow = Model::cloneResultMapHydrate($row, $columnMap, $hydrateMode);
				break;
		}

		if ($activeRow instanceof \Phalcon\Mvc\Model && !empty($this->getWith())){
			$activeRow->load($this->getWith());
		}

		$this->_activeRow = $activeRow;

		return $activeRow;
	}

	/**
	 * Serializing a resultset will dump all related rows into a big array
	 */
	public function serialize()
	{
		/**
		 * Serialize the cache using the serialize function
		 */
		return serialize([
			"model"         => $this->_model,
			"cache"         => $this->_cache,
			"rows"          => $this->toArray(false),
			"columnMap"     => $this->_columnMap,
			"hydrateMode"   => $this->_hydrateMode,
			"keepSnapshots" => $this->_keepSnapshots
		]);
	}

	/**
	 * Unserializing a resultset will allow to only works on the rows present in the saved state
	 */
	public function unserialize($data)
	{
		$resultset = unserialize($data);
		if (!is_array($resultset)) {
			throw new \Exception("Invalid serialization data");
		}

		$this->_model = resultset["model"];
		$this->_rows = resultset["rows"];
		$this->_count = count(resultset["rows"]);
		$this->_cache = resultset["cache"];
		$this->_columnMap = resultset["columnMap"];
		$this->_hydrateMode = resultset["hydrateMode"];

		if (isset($resultset["keepSnapshots"])) {
			$this->_keepSnapshots = $resultset["keepSnapshots"];
		}
	}



	public function with($with)
	{
		$this->with = $with;
		return $this;
	}

	public function getWith()
	{
		return $this->with;
	}

	public function toArray($renameColumns = true)
	{

		$records = [];

		if (!is_array($this->_rows)) {
			$result = $this->_result;
			if ($this->_row !== null) {
				// re-execute query if required and fetchAll rows
				$result->execute();
			}
			$this->_row = null;
			$this->_rows = $result->fetchAll(); // keep result-set in memory
		}

		foreach ($this as $key => $model) {

			$record = $model->toArray();

			if ($renameColumns && is_array($this->_columnMap)) {
				$record = $this->mapColumns($record);
			}

			$records[] = $record;
		}

		return $records;
	}

	public function max($column)
	{
		$max = 0;
		foreach ($this as $item) {
			if ($item->{$column} > $item){
				$max = $item->{$column};
			}
		}
		return $max;
	}

	public function min($column)
	{
		$min = 0;
		foreach ($this as $item) {
			if ($item->{$column} < $item){
				$min = $item->{$column};
			}
		}
		return $min;
	}

	public function sum($column)
	{
		$sum = 0;
		foreach ($this as $item) {
			$sum += $item->{$column};
		}
		return $sum;
	}

	public function pluck($value, $key = null)
	{
		return collect(array_pluck($this->toArray(), $value, $key));
	}

	protected function mapColumns($record)
	{
		$columnMap = $this->_columnMap;

		$renamed = [];
		foreach ($record as $key => $value) {

			$renamedKey = $columnMap[$key];
			if (empty($renamedKey)) {
				throw new \Exception("Column '" . $key . "' is not part of the column map");
			}

			if (is_array($renamedKey)) {

				$renamedKey = $renamedKey[0];
				if (empty($renamedKey))  {
					throw new \Exception("Column '" . $key . "' is not part of the column map");
				}
			}

			$renamed[$renamedKey] = $value;
		}

		return $renamed;
	}

}