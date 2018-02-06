<?php

if (! function_exists('camelize')) {
	function camelize($text, $delimiter = null)
	{
		return \Phalcon\Text::camelize($text, $delimiter);
	}
}

if (! function_exists('uncamelize')) {
	function uncamelize($text, $delimiter = null)
	{
		return \Phalcon\Text::uncamelize($text, $delimiter);
	}
}

if (! function_exists('toHtml')) {
	function toHtml ($value)
	{
		return html_entity_decode($value, ENT_QUOTES, "UTF-8");
	}
}

if (! function_exists('toSafeHtml')) {
	function toSafeHtml ($value)
	{
		return htmlentities($value, ENT_QUOTES, "UTF-8", false);
	}
}

if (! function_exists('numberUnformat')) {
	function numberCleanFormat($text, $thousands_sep = ",")
	{
		$text = strval($text);
		return str_replace($thousands_sep, "", $text);
	}
}

if (! function_exists('str_contains')) {
	function str_contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle) {
			if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}
}

if (! function_exists('str_limit')) {
	function str_limit($value, $limit = 100, $end = '...')
	{
		if (mb_strwidth($value, 'UTF-8') <= $limit) {
			return $value;
		}
		return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
	}
}

if (! function_exists('str_parse_callback')) {
	function str_parse_callback($callback, $default = null, $needles = "::")
	{
		return str_contains($callback, $needles) ? explode($needles, $callback, 2) : [$callback, $default];
	}
}