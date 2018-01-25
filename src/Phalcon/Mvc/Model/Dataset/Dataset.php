<?php

namespace Apps\Core;

use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class Dataset implements \IteratorAggregate
{

	protected $data;

	protected $paginated_data;

	protected $paginator_class;

	protected $paginator_config = [];

	protected $paginator_instance;

	public function __construct($data)
	{
		$this->setData($data);
	}

	public function paginate($pageSize = 10, $page = 1)
	{
		$this->paginated_data = $this->getPaginateModel($pageSize, $page)->getPaginate();
		$cls = $this->getPaginator();
		$this->paginator_instance = new $cls($this->paginated_data, $this->getPaginatorConfig());
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $data
	 * @return Dataset
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	public function paginator()
	{
		return $this->paginator_instance;
	}

	/**
	 * @return mixed
	 */
	public function getPaginator()
	{
		return $this->paginator_class;
	}

	/**
	 * @param mixed $paginator
	 * @return Dataset
	 */
	public function setPaginator($paginator, array $config = [])
	{
		$this->paginator_class = $paginator;
		$this->paginator_config = $config;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getPaginatorConfig()
	{
		return $this->paginator_config;
	}

	/**
	 * @param array $config
	 * @return Dataset
	 */
	public function setPaginatorConfig($config)
	{
		$this->paginator_config = $config;
		return $this;
	}

	public function __call($name, $arguments)
	{
		if (is_object($this->paginator_instance) && method_exists($this->paginator_instance, $name)){
			return call_user_func_array([$this->paginator_instance, $name], $arguments);
		}
	}

	public function getIterator()
	{
		if (!is_null($this->paginated_data)){
			return new \ArrayIterator($this->paginated_data->items);
		}
		return $this->getData();
	}

	public function getTotalItems()
	{
		if (is_object($this->paginator_instance)){
			return $this->paginator_instance->getTotalItems();
		}

		if ($this->data){
			return $this->data->count();
		}

		return 0;
	}

	public function getListItemsFirstIndex()
	{
		if (is_object($this->paginator_instance)){
			return $this->paginator_instance->getListItemsFirstIndex();
		}

		return 1;
	}


	protected function getPaginateModel($pageSize = 10, $page = 1)
	{
		return new PaginatorModel(
			[
				'data'  => $this->getData(),
				'limit' => $pageSize,
				'page'  => $page,
			]
		);
	}



}