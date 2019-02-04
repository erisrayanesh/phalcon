<?php

namespace Phalcon\Auth\Drivers;

use Phalcon\Auth\Authenticatable;

interface Driver
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check();

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest();

    /**
     * Get the currently authenticated user.
     *
     * @return \Phalcon\Auth\Authenticatable|null
     */
    public function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id();

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []);

    /**
     * Set the current user.
     *
     * @param \Phalcon\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user);
}
