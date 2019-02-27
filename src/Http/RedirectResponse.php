<?php

namespace Phalcon\Http;

use Phalcon\Http\Request\FileInterface;
use Phalcon\Text;
use \Phalcon\Validation\Message\Group as ValidationMessageGroup;

class RedirectResponse extends Response
{
	use ResponseTrait;

	public function __construct($location = null, $code = 302, $headers = [], $externalRedirect = false)
	{
		parent::__construct(null, 302);

		if (!empty($headers)) {
			$this->withHeaders($headers);
		}

		$this->redirect($location, $externalRedirect, $code);
	}

	public function setContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('The content cannot be set on a FileResponse instance.');
		}
	}

	public function appendContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('Can not set append content on a FileResponse instance.');
		}
	}

	public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
	{
		if (null !== $content) {
			throw new \LogicException('The json content cannot be set on a FileResponse instance.');
		}
	}

	public function setFileToSend($filePath, $attachmentName = null, $attachment = true)
	{
		if (null !== $filePath) {
			throw new \LogicException('Can not send a file on a RedirectResponse instance.');
		}
	}

	public function setFile($file)
	{
		if (null !== $file) {
			throw new \LogicException('Can not set file on a RedirectResponse instance.');
		}
	}

	/**
	 * Flash a piece of data to the session.
	 *
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return \Phalcon\Http\RedirectResponse
	 */
	public function with($key, $value = null)
	{
		$key = is_array($key) ? $key : [$key => $value];

		foreach ($key as $k => $v) {
			session()->set($k, $v);
		}

		return $this;
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @param  array  $input
	 * @return $this
	 */
	public function withInput(array $input = null)
	{
		flashInputs()->set($this->removeFilesFromInput(
			!is_null($input) ? $input : request()->get()
		));

		return $this;
	}

	/**
	 * Flashes an array of input to the session.
	 *
	 * @return \Phalcon\Http\RedirectResponse
	 */
	public function onlyInput()
	{
		return $this->withInput(request_only(func_get_args()));
	}

	/**
	 * Flashes an array of input to the session.
	 *
	 * @return \Phalcon\Http\RedirectResponse
	 */
	public function exceptInput()
	{
		return $this->withInput(request_except(func_get_args()));
	}

	public function withMessage($type, $message = null)
	{
		if ($type instanceof ValidationMessageGroup){
			$message = $this->parseValidationMessageGroup($type);
			$type = "error";
		}

		if (!is_array($type)){
			$type = [$type => $message];
		}

		foreach ($type as $t => $messages){

			if (!is_string($t)){
				throw new \InvalidArgumentException('Can not flash message with unknown type.');
			}

			$messages = array_wrap($messages);

			foreach ($messages as $item){
				flashSession()->{$t}($item);
			}

		}

		return $this;
	}

	protected function parseValidationMessageGroup(ValidationMessageGroup $messages)
	{
		$errors = [];
		foreach ($messages as $err){
			$errors[] = $err->getMessage();
		}
		return $errors;
	}

	public function __call($method, $arguments)
	{
		if (Text::startsWith($method, 'with')) {
			$type = strtolower(substr($method, 4));
			$this->withMessage($type, $arguments[0]);
		}

		["error", "notice", "success", "warning"];
	}

	/**
	 * Remove all uploaded files form the given input array.
	 *
	 * @param  array  $input
	 * @return array
	 */
	protected function removeFilesFromInput(array $input)
	{
		foreach ($input as $key => $value) {
			if (is_array($value)) {
				$input[$key] = $this->removeFilesFromInput($value);
			}

			if ($value instanceof FileInterface) {
				unset($input[$key]);
			}
		}

		return $input;
	}
}