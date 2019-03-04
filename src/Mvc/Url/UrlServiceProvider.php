<?php

namespace Phalcon\Mvc\Url;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Url;

class UrlServiceProvider implements ServiceProviderInterface
{

	public function register(DiInterface $di)
	{
		$baseUri = $this->getBaseUri();

		$di->setShared('url', function () use ($baseUri) {
			$url = new Url();
			$url->setBaseUri($baseUri);
			return $url;
		});
	}

	protected function getBaseUri()
	{
		$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
		$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
		$port = ($port) ? ':' . $_SERVER["SERVER_PORT"] : '';
		$url = ($isHTTPS ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . $port;

		$file = $_SERVER["SCRIPT_NAME"];
		$file = explode("/", $file);
		array_pop($file);

		return trim($url . implode("/", $file), '\\\/') . "/";
	}

}