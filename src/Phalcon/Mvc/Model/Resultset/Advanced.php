<?php

namespace Phalcon\Mvc\Model\Resultset;

use Phalcon\Mvc\Model\Resultset\Simple;

class Advanced extends Simple
{
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