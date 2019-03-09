<?php


namespace Phalcon\Auth\Access;


interface Authorizable
{

	/**
	 * Determine if the entity has a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function can($ability, $arguments = []);

	/**
	 * Determine if the entity does not have a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function cant($ability, $arguments = []);

	/**
	 * Determine if the entity does not have a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function cannot($ability, $arguments = []);

}