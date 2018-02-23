<?php

function route($name, $data = null, $query = null)
{
	if (is_null($data)){
		$data = [];
	}

	if (!is_array($data)){
		$data = [$data];
	}

	$data["for"] = $name;
	return url()->get($data, $query);
}

function request_only($list)
{

	if (!is_array($list)){
		$list = func_get_args();
	}

	if (!is_array($list)){
		$list = [];
	}

	$values = [];
	foreach ($list as $item){
		$values[$item] = request()->get($item);
	}
	return $values;
}

function request_except($list)
{

	if (!is_array($list)){
		$list = func_get_args();
	}

	if (!is_array($list)){
		$list = [];
	}

	$keys = array_keys(array_except($_REQUEST, $list));

	$values = [];
	foreach ($keys as $item){
		$values[$item] = request()->get($item);
	}
	return $values;
}

function request_expects_json()
{
	return (request()->isAjax() && ! request_is_pjax()) || request_wants_json();
}

function request_wants_json()
{
	$acceptable = request()->getBestAccept();

	return isset($acceptable[0]) && str_contains($acceptable[0], ['/json', '+json']);
}

function request_is_pjax()
{
	return request()->getHeader('X-PJAX') == true;
}

function previous_request_url()
{
	return old("_url", "/");
}