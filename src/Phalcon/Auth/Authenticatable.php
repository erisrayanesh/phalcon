<?php

namespace Phalcon\Auth;


interface Authenticatable
{
	public function findAuthenticatable($id);

	public function getAuthenticatableName();

	public function getAuthenticatableIdentityKey();

	public function getAuthenticatableIdentity();

}