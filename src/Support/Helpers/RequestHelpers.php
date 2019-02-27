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
	return url($data, $query);
}

function previous_request_url($fallback = false)
{

	$referrer = request()->getHTTPReferer();

	//TODO: check _url value
	$url = $referrer ? url($referrer) : session()->get('_url');

	if ($url) {
		return $url;
	} elseif ($fallback) {
		return url($fallback);
	}

	return url('/');

	//return trim(old("_url"), '\/\\') ?? ($fallback ?: null);
}