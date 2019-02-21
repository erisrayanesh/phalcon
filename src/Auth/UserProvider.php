<?php

namespace Phalcon\Auth;

interface UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Phalcon\Auth\Authenticatable|null
     */
    public function findById($identifier);

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Phalcon\Auth\Authenticatable|null
     */
    public function findByToken($identifier, $token);

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Phalcon\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token);

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Phalcon\Auth\Authenticatable|null
     */
    public function findByCredentials(array $credentials);

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Phalcon\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials);
}
