<?php

if (!function_exists('dd')){
	function dd($var)
	{
		array_map(function ($x) {
			dump($x);
		}, func_get_args());
		die(1);
	}
}

function flash_error($errorKey = null, $error = null)
{

	if ($errorKey instanceof \Phalcon\Validation\Message\Group){
		$error = '<ul>';
		foreach ($errorKey as $err){
			$error .= "<li>$err</li>";
		}
		$error .= '</ul>';
		$errorKey = "error";
	}

	if (!is_array($errorKey) && !is_null($error)){
		$errorKey = [$errorKey => $error];
	}

	if (is_null($errorKey)){
		$errorKey = [];
	}

	foreach ($errorKey as $key => $msg){
		if (method_exists(flashSession(), $key)){

			if (!is_array($msg)){
				$msg = [$msg];
			}

			foreach ($msg as $item){
				flashSession()->{$key}($item);
			}
		}
	}
}

function redirect($location, $withKey = null, $with = null)
{
	flash_error($withKey, $with);
	return response()->redirect($location);
}

function redirect_back($withKey = null, $with = null)
{
	return redirect(trim(old("_url"), '\/\\'), $withKey, $with);
}

function redirect_route($name, $data = null, $query = null, $withKey = null, $with = null)
{
	return redirect(route($name, $data, $query), $withKey, $with);
}

function abort($code)
{
	throw new \Phalcon\Http\Response\Exception('', $code);
}
