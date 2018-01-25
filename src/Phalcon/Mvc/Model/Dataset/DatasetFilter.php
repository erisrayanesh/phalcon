<?php

namespace Phalcon\Mvc\Model\Dataset;

use Phalcon\Support\Collection;

class DatasetFilter extends Collection
{

	protected $filterables = [];

	public function __construct(array $items = [], $checkEmpty = true)
	{
		parent::__construct([]);
		$this->setFilterables($items);
		$this->read($checkEmpty);
	}

	/**
	 * @return array
	 */
	public function getFilterables()
	{
		return $this->filterables;
	}

	/**
	 * @param array $filterables
	 * @return $this
	 */
	public function setFilterables($filterables)
	{
		$this->filterables = $filterables;
		return $this;
	}

	public function read ($checkEmpty = true)
	{

		$filters = collect(request_only($this->filterables));
		if ($checkEmpty){
			$filters = $filters->reject(function ($value) {
				if (is_array($value)){
					return empty($value);
				}
				return strlen($value) == 0;
			});
		}

		$this->items = $filters->toArray();
	}

}