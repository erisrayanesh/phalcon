<?php

namespace Phalcon\Paginator;


abstract class PaginatorUIProcessor
{

	protected $config = [
		"first" => "first",
		"next" => "next",
		"prev" => "previous",
		"last" => "last",
		"numbers" => true,
		"var" => "page",
	];

	protected $paginate;

	protected $link;

	protected $appendix = [];

	public abstract function render();

	/**
	 * BootstrapFourPaginator constructor.
	 * @param array $config
	 */
	public function __construct($paginate, array $config = [])
	{
		$this->setPaginate($paginate);
		$this->setConfig($config);
	}

	/**
	 * Returns paginator config
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Sets paginator config
	 * @param array $config
	 */
	public function setConfig($config)
	{
		$this->config = array_merge($this->config, $config);
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPaginate()
	{
		return $this->paginate;
	}

	/**
	 * @param mixed $paginate
	 */
	public function setPaginate($paginate)
	{
		$this->paginate = $paginate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param $link
	 * @return static
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}

	/**
	 * @param $name
	 * @param null $data
	 * @return static
	 */
	public function setRoute($name, $data = null)
	{
		$this->link = route($name, $data);
		return $this;
	}

	public function append($data)
	{
		$this->appendix = http_build_query($data);
		return $this;
	}

	public function resetAppendix()
	{
		$this->appendix = '';
		return $this;
	}


	public function __get($name)
	{
		return value(array_get($this->config, $name));
	}

	public function __set($name, $value)
	{
		return $this->config[$name] = $value;
	}

	public function noFirstLast()
	{
		$this->first = null;
		$this->last = null;
		return $this;
	}

	public function noNextPrev()
	{
		$this->next = null;
		$this->prev = null;
		return $this;
	}

	public function noNumbers()
	{
		$this->numbers = false;
		return $this;
	}

	public function getPageLink($page)
	{
		$page = $this->var . "=" . $page;
		$query = $this->appendix? $this->appendix . "&" . $page : $page ;
		return $this->link . "?" . $query;
	}

	public function isAvailable()
	{
		return $this->getPaginate()->total_pages > 1;
	}

	public function isFirstPage()
	{
		return $this->getPaginate()->current == $this->getPaginate()->first;
	}

	public function hasPreviousPage()
	{
		return $this->getPaginate()->current > $this->getPaginate()->before;
	}

	public function hasNextPage()
	{
		return $this->getPaginate()->current < $this->getPaginate()->next;
	}

	public function isLastPage()
	{
		return $this->getPaginate()->current == $this->getPaginate()->last;
	}

	public function isCurrentPage($page)
	{
		return $this->getPaginate()->current == $page;
	}

	public function getTotalItems()
	{
		return $this->getPaginate()->total_items;
	}

	public function getTotalPages()
	{
		return $this->getPaginate()->total_pages;
	}

	public function getPageSize()
	{
		return $this->getPaginate()->limit;
	}

	public function getCurrentPage()
	{
		return $this->getPaginate()->current;
	}

	public function getListItemsFirstIndex()
	{
		return (($this->getCurrentPage() -1) * $this->getPageSize()) + 1;
	}

}