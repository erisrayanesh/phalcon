<?php
namespace Apps\Core\Components;


use Apps\Core\ErrorsBag;
use Phalcon\Mvc\User\Component;
use Phalcon\Validation\Message;

class FlashInputs extends Component
{

	protected $inputsNewVar = "__inputs.new";
	protected $inputsOldVar = "__inputs.old";
	protected $errorsVar    = "__errors";

	public function init()
	{
		$new = $this->session->get($this->inputsNewVar, []);
		$this->session->set($this->inputsOldVar, $new);

		$this->session->set($this->inputsNewVar, $_REQUEST);

		$this->view->setVar("errors", new ErrorsBag($this->session->get($this->errorsVar)));
		$this->forgetErrors();
	}

	/**
	 * @param $key
	 * @param $default
	 * @return mixed|null
	 */
	public function getOld($key = null, $default = null)
	{
		$old = $this->session->get($this->inputsOldVar);
		if ($key == null)
		{
			return $old;
		}

		return isset($old[$key])? $old[$key] : $default;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function hasOld($key)
	{
		return $this->getOld($key) != null;
	}


	/**
	 * @param $key
	 * @param $message
	 * @return static
	 */
	public function addError($key, $message = null)
	{
		if (is_array($key)){
			return $this->addErrors($key);
		}

		if (is_null($message)){
			return $this;
		}

		$errors = $this->session->get($this->errorsVar);
		$errors[] = [$key, $message];
		$this->session->set($this->errorsVar, $errors);

		return $this;
	}

	public function addErrors($errors)
	{
		foreach ($errors as $index => $error) {
			if ($error instanceof Message) {
				$this->addValidationError($error);
				continue;
			}

			if (is_array($error)){
				$this->addError($error[0], $error->getMessage());
			}
		}
	}

	public function addValidationError(Message $message)
	{
		$this->addError($message->getField(), $message->getMessage());
	}

	public function forgetErrors()
	{
		$this->session->set($this->errorsVar, []);
	}


}