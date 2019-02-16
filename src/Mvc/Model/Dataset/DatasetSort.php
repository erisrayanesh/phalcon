<?php

namespace Phalcon\Mvc\Model\Dataset;


use Phalcon\Support\Collection;

class DatasetSort
{

	CONST ASC = "asc";
	CONST DESC = "desc";

	protected $sortables;

	protected $actives;

	public function __construct($sortables, $sortBy)
	{
		$this->setSortables($sortables);
		$this->sort($sortBy);
	}

	/**
	 * @return Collection
	 */
	public function getSortables()
	{
		return $this->sortables;
	}

	/**
	 * @param mixed $sortables
	 * @return Dataset
	 */
	public function setSortables($sortables)
	{
		$this->sortables = collect($sortables);

		return $this;
	}

	/**
	 * @return Collection
	 */
	public function getSortedBy()
	{
		return $this->actives;
	}

	/**
	 * @param array $actives
	 * @return $this
	 */
	public function sort($sortBy)
	{
		$this->actives = collect($sortBy);
		$this->actives->reject(function($value, $key){
			$value = strtolower(trim($value));
			return !$this->getSortables()->has($key) || !self::isDirection($value);
		});
		return $this;
	}

	public function toString()
	{
		$sort = [];
		foreach ($this->getSortedBy() as $field => $dir){
			$sort[] = "$field $dir";
		}
		return implode(", ", $sort);
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function result()
	{
		$result = [];

		foreach ($this->getSortedBy() as $key => $item){
			$result[$key] = [
				'sorted' => true,
				'dir' => $item,
				'title' => $this->getSortables()->get($key),
			];
		}

		$this->getSortables()->except($this->getSortedBy()->keys()->toArray())->each(function($value, $key) use (&$result){
			$result[$key] = [
				'sorted' => false,
				'dir' => 'asc',
				'title' => $value,
			];
		});

		return $result;
	}


	public static function isDirection($value)
	{
		return $value === self::ASC || $value === self::DESC;
	}
}