<?php

namespace Phalcon\Http;

class FormRequest extends Request
{

	public function old($key = null, $default = null)
	{
		return flashInputs()->getOld($key, $default);
	}

	public function flash()
	{
		flashInputs()->set($this->get());
		return $this;
	}

	public function flush()
	{
		flashInputs()->flush();
		return $this;
	}

	/**
	 * Flash only some of the input to the session.
	 *
	 * @param  array|mixed  $keys
	 * @return void
	 */
	public function flashOnly($keys)
	{
		flashInputs()->set($this->only(is_array($keys) ? $keys : func_get_args()));
	}

	/**
	 * Flash only some of the input to the session.
	 *
	 * @param  array|mixed  $keys
	 * @return void
	 */
	public function flashExcept($keys)
	{
		flashInputs()->set($this->except(is_array($keys) ? $keys : func_get_args()));
	}

	public function only($keys)
	{

		$results = [];
		$input = $this->get();
		$placeholder = new \stdClass;

		foreach (is_array($keys) ? $keys : func_get_args() as $key) {
			$value = data_get($input, $key, $placeholder);

			if ($value !== $placeholder) {
				array_set($results, $key, $value);
			}
		}

		return $results;
	}

	public function except($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();

		$results = $this->get();

		array_forget($results, $keys);

		return $results;
	}

	public function expectsJson()
	{
		return ($this->isAjax() && ! $this->isPjax()) || $this->wantsJson();
	}

	public function wantsJson()
	{
		$acceptable = $this->getBestAccept();

		if (is_array($acceptable)) {
			foreach ($acceptable as $acc) {
				$acceptable = $acc;
				break;
			}
		}

		return str_contains($acceptable, ['/json', '+json']);
	}

	public function isPjax()
	{
		return $this->getHeader('X-PJAX') == true;
	}

	/**
	 * Get the client IP address.
	 *
	 * @return string
	 */
	public function ip()
	{
		return $this->getClientAddress();
	}
}