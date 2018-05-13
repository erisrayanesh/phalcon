<?php

namespace Phalcon\Mvc\Model\Resultset;

use Phalcon\Mvc\Model\Resultset\Simple;

class Advanced extends Simple
{

	protected $with = null;

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

			if (!empty($this->getWith())){
				$model->load($this->getWith());
			}

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