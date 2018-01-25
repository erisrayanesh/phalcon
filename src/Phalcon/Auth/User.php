<?php

namespace Phalcon\Auth;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Traits\Authenticatable as ModelAuthenticatable;
use Phalcon\Mvc\Model\Traits\Authorizable as ModelAuthorizable;

class User extends Model implements Authenticatable, Authorizable
{
	use ModelAuthorizable, ModelAuthenticatable;
}