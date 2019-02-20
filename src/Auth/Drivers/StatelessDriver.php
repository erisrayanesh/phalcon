<?php

namespace Phalcon\Auth\Drivers;

use Phalcon\Auth\Authenticatable;
use Phalcon\Auth\Driver;

interface StatelessDriver extends Driver
{
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @return bool
     */
    public function attempt(array $credentials = []);

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = []);

    /**
     * Log a user into the application.
     *
     * @param  \Phalcon\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user);

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @param  bool   $remember
     * @return \Phalcon\Auth\Authenticatable
     */
    public function loginUsingId($id);

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function onceUsingId($id);

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout();
}
