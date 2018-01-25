<?php

namespace Phalcon\Mvc\Model\Traits;

use Phalcon\Events\Manager;

trait HasEvents
{

	public function initEvents()
	{
		if (!$this->getEventsManager()){
			$this->setEventsManager(new Manager());
		}

		$traits = class_uses($this);
		foreach ($traits as $trait){
			$items = explode('\\', $trait);
			$method = 'init' . end($items);
			if (method_exists($this, $method)){
				call_user_func([$this, $method]);
			}
		}


	}

}