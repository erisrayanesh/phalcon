<?php

namespace Phalcon\Translate;

use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

abstract class Loader implements LoaderInterface
{


	protected function convertToArray($definitions)
	{
		if (empty($definitions)) {
			return [];
		}

		if (is_array($definitions)) {
			return $definitions;
		}

		if ($definitions instanceof Arrayable) {
			return $definitions->toArray();
		}

		if ($definitions instanceof Jsonable) {
			$definitions = $definitions->toJson();
		}

		if (is_string($definitions)) {
			return json_decode($definitions, true);
		}

		return (array) $definitions;
	}
}