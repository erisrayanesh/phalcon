<?php

namespace Phalcon\Paginator\Processors;

use Phalcon\Paginator\PaginatorUIProcessor;

class BootstrapFourPaginator extends PaginatorUIProcessor
{

	public function render()
	{

		if (!$this->isAvailable()){
			return "";
		}


		$output = '<ul class="pagination justify-content-center">';

		if ($this->first){
			$output .= $this->createItem(
				!$this->isFirstPage(),
				!$this->isFirstPage()? $this->getPageLink($this->getPaginate()->first) : "#",
				$this->first
			);
		}

		if ($this->prev){
			$output .= $this->createItem (
				$this->hasPreviousPage(),
				$this->hasPreviousPage()? $this->getPageLink($this->getPaginate()->before) : "#",
				$this->prev
			);
		}


		if ($this->numbers){

//			for ($i = $this->getPaginate()->first; $i <= $this->getPaginate()->last; $i++){
			foreach ($this->getPages() as $page){
				$output .= $this->createItem (
					!$page['is_current'],
					!$page['is_current']? $page['url'] : "#",
					$page['num']
				);
			}
		}


		if ($this->next){
			$output .= $this->createItem (
				$this->hasNextPage(),
				$this->hasNextPage()? $this->getPageLink($this->getPaginate()->next) : "#",
				$this->next
			);
		}

		if ($this->last){
			$output .= $this->createItem(
				!$this->isLastPage(),
				!$this->isLastPage()? $this->getPageLink($this->getPaginate()->last) : "#",
				$this->last
			);
		}

		$output .= '</ul>';
		return $output;
	}

	protected function createItem($enable = true, $link = "#", $html = "")
	{
		return '<li class="page-item ' . (!$enable? 'disabled' : '') . '">
						<a class="page-link" href="' . $link . '">' . $html . '</a>
					</li>';
	}

}