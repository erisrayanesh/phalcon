<?php

namespace Phalcon\Mvc\Controller;

use Phalcon\Http\JsonResponse;
use Phalcon\Validation;
use Phalcon\Validation\ValidationException;
use \Phalcon\Validation\Message\Group;

trait ValidatesRequests
{

	public function validate($rules, $values)
	{
		$validator = $this->getValidationFactory();
		$this->appendRulesToValidator($validator, $rules);
		$messages = $validator->validate($values);

		if (count($messages)){
			$this->throwValidationException($validator, $messages);
		}

	}

	/**
	 * Instantiates a validation class
	 * @return \Phalcon\Validation
	 */
	protected function getValidationFactory()
	{
		return new Validation();
	}

	protected function throwValidationException(Validation $validator, Group $messages)
	{
		throw new ValidationException($validator, $this->buildFailedValidationResponse($messages));
	}

	protected function buildFailedValidationResponse(Group $messages)
	{
		if (request()->expectsJson()) {
			return new JsonResponse($this->formatValidationMessagesForJson($messages), 422);
		}

		inputs()->addErrors($messages);
		flash_error($messages);
		return $this->redirectFailedValidation();
	}

	protected function appendRulesToValidator(Validation $validator, array $rules)
	{
		foreach ($rules as $rule) {
			$validator->add($rule[0], $rule[1]);
		}
	}

	protected function formatValidationMessagesForJson(Group $messages)
	{
		$arr = [];
		foreach ($messages as $message) {
			$arr[] = [
				$message->getField(),
				$message->getMessage()
			];
		}
		return $arr;
	}

	protected function redirectFailedValidation()
	{
		return redirect_back();
	}

}