<?php

namespace Phalcon\Auth\Drivers;

use Phalcon\Auth\AuthenticationException;
use Phalcon\Auth\Authenticatable;
use Phalcon\Auth\UserProvider;

trait DriverHelpers
{
    /**
     * The currently authenticated user.
     *
     * @var \Phalcon\Auth\Authenticatable
     */
    protected $user;

    /**
     * The user provider implementation.
     *
     * @var UserProvider
     */
    protected $userResolver;

    /**
     * Determine if current user is authenticated. If not, throw an exception.
     *
     * @return \Phalcon\Auth\Authenticatable
     *
     * @throws \Phalcon\Auth\AuthenticationException
     */
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException();
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return !is_null($this->user);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * Set the current user.
     *
     * @param  \Phalcon\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return UserProvider
     */
    public function getUserResolver()
    {
        return $this->userResolver;
    }

	/**
	 * @param UserProvider $userResolver
	 */
    public function setUserResolver(UserProvider $userResolver)
    {
        $this->userResolver = $userResolver;
    }
}
