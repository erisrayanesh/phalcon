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

/**
 * @param string $location
 * @param int $code
 * @param array $headers
 * @param bool $external
 * @return \Phalcon\Http\RedirectResponse
 */
function redirect($location, $code = 302, $headers = [], $external = false)
{
	return response()->redirectTo($location, $code, $headers, $external);
}

/**
 * @param int $code
 * @param array $headers
 * @param bool $fallback
 * @return \Phalcon\Http\RedirectResponse
 */
function redirect_back($code = 302, $headers = [], $fallback = false)
{
	return redirect(previous_request_url($fallback), $code, $headers);
}

/**
 * @param $name
 * @param null $data
 * @param null $query
 * @param int $code
 * @param array $headers
 * @return \Phalcon\Http\RedirectResponse
 */
function redirect_route($name, $data = null, $query = null, $code = 302, $headers = [])
{
	return redirect(route($name, $data, $query), $code, $headers);
}

function abort($code)
{
	throw new \Phalcon\Http\Response\Exception('', $code);
}

function getView($view, array $params = null)
{

	$renderView = clone view();

	if (!empty($params) && is_array($params)) {
		$renderView->setVars($params);
	}

	$renderView->reset();
	$renderView->pick($view);
	$renderView->start();
	$renderView->render(dispatcher()->getControllerName(), dispatcher()->getActionName());
	$renderView->finish();

	return $renderView;

}