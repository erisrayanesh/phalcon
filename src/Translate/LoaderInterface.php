<?php

namespace Phalcon\Translate;


interface LoaderInterface
{
	public function load($language, $group);
}