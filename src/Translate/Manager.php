<?php
namespace Phalcon\Translate;

use \Phalcon\Support\Manager as BaseManager;
use Phalcon\Translate\Interpolator\AssociativeArray;

class Manager extends BaseManager
{

	protected $driverType = LoaderInterface::class;

	protected $namespace = "Phalcon\\Translate\\Loader";

	/**
	 * The default locale being used by the translator.
	 *
	 * @var string
	 */
	protected $locale = "en_US";

	/**
	 * The fallback locale used by the translator.
	 *
	 * @var string
	 */
	protected $fallback;

	/**
	 * Cached
	 * @var array
	 */
	protected $loaded = [];

	/**
	 * @var AssociativeArray
	 */
	protected $interpolator;

	public function __construct()
	{
		$this->interpolator = new AssociativeArray();
	}

	public function __call($method, $parameters)
	{
		throw new \BadMethodCallException('Method ' . $method . ' not found');
	}

	public function get($key, $placeholders = [], $local = null)
	{
		$local = $local ?? $this->getLocale();
		[$group, $item] = $this->parseKey($key);
		$this->load($local, $group);
		$definition = array_get($this->loaded[$local][$group], $item);

		if (empty($definition)) {
			return $key;
		}

		return $this->interpolator->replacePlaceholders($definition, $placeholders);
	}

	public function load($locale, $group)
	{
		if ($this->isLoaded($locale, $group)) {
			return;
		}

		$definitions = [];

		foreach ($this->drivers as $key => $driver) {
			$definitions = array_merge($definitions, $this->driver($key)->load($locale, $group));
		}

		$this->loaded[$locale][$group] = $definitions;
	}

	/**
	 * Returns current locale name
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * Sets current locale
	 * Sets current locale
	 * @param string $locale
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}

	/**
	 * @return string
	 */
	public function getFallback()
	{
		return $this->fallback;
	}

	/**
	 * @param string $fallback
	 * @return Manager
	 */
	public function setFallback($fallback)
	{
		$this->fallback = $fallback;
		return $this;
	}

	protected function isLoaded($locale, $group)
	{
		return isset($this->loaded[$locale][$group]);
	}

	protected function parseKey($key)
	{
		if (strpos($key, ".") === false){
			return [null, $key];
		}

		$segments = explode('.', $key);
		return [$segments[0], implode('.', array_slice($segments, 1))];
	}

}