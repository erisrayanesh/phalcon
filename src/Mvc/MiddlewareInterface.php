<?php

namespace Phalcon\Mvc;

interface MiddlewareInterface
{
	public function handle($request, \Closure $next);
}