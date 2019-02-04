<?php

namespace Phalcon\Auth\UserResolvers;

use Phalcon\Auth\Authenticatable;
use Phalcon\Mvc\Model;
use Phalcon\Support\Interfaces\Arrayable;

class ModelResolver implements UserResolver
{

	/**
	 * @var Model
	 */
	protected $model;

	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	public function findById($identifier)
	{
		$model = $this->createModel();
		$method = "findFirstBy" . camelize($model->getAuthIdentifierName());
		return $model->{$method}($identifier);
	}

	public function findByToken($identifier, $token)
	{
		$model = $this->findById($identifier);

		if (!$model) {
			return null;
		}

		$rememberToken = $model->getRememberToken();

		return $rememberToken && hash_equals($rememberToken, $token) ? $model : null;
	}

	public function updateRememberToken(Authenticatable $user, $token)
	{
		$user->setRememberToken($token);

		$timestamps = $user->timestamps;

		$user->timestamps = false;

		$user->save();

		$user->timestamps = $timestamps;
	}

	public function findByCredentials(array $credentials)
	{
		if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
			return;
		}

		$class = $this->getClearedModelName();
		$query = $class::query();

		foreach ($credentials as $key => $value) {
			if (str_contains('password', $key)) {
				continue;
			}

			if (is_array($value) || $value instanceof Arrayable) {
				$query->whereIn($key, $value->toArray());
			} else {
				$query->where("$key = :$key:", [$key => $value]);
			}
		}

		return $query->execute()->getFirst();
	}

	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		$plain = $credentials['password'];
		return security()->checkHash($plain, $user->getAuthPassword());
	}

	public function getModel()
	{
		return $this->model;
	}

	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	public function getClearedModelName()
	{
		return '\\'.ltrim($this->model, '\\');
	}

	public function createModel()
	{
		$class = $this->getClearedModelName();
		return new $class;
	}

}