<?php

namespace Phalcon\Translate\NativeArrayLoader;

abstract class Loader implements NativeArrayLoaderInterface
{

	protected $di;

	public function __construct(\Phalcon\DiInterface $dependencyInjector)
	{
		$this->di = $dependencyInjector;
	}
}