<?php

namespace Phalcon\Mvc\Controller\Traits;


use Phalcon\Http\Response;
use Phalcon\Validation;
use Phalcon\Validation\Exceptions\ValidationException;
use \Phalcon\Validation\Message\Group;

trait ValidatesRequests
{

	public function validate($rules, $values)
	{
		$validator = $this->getValidationFactory();

		$this->appendRulesToValidatior($validator, $rules);

		$messages = $validator->validate($values);

		if (count($messages)){
			$this->throwValidationException($validator, $messages);
		}

	}

	protected function throwValidationException(Validation $validator, Group $messages)
	{
		throw new ValidationException($validator, $this->buildFailedValidationResponse($messages));
	}

	protected function buildFailedValidationResponse(Group $messages)
	{
		if (request_expects_json()) {
			$jsonResponse = new Response('', 422);
			$jsonResponse->setJsonContent($this->formatValidationMessagesForJson($messages));
			return $jsonResponse;
		}

		inputs()->addErrors($messages);
		flash_error($messages);
		return $this->redirectFailedValidation();
	}


	protected function appendRulesToValidatior(Validation &$validator, array $rules)
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

	/**
	 * @return \Phalcon\Validation
	 */
	protected function getValidationFactory()
	{
		return new Validation();
	}

	protected function redirectFailedValidation()
	{
		return redirect_back();
	}

}