<?php

namespace Phalcon\Support\Interfaces;


interface Jsonable
{
	public function toJson($options = 0, $depth = 512);
}