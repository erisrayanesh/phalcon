<?php

namespace Phalcon\Auth;

use Phalcon\Auth\Access\Authorizable;
use Phalcon\Mvc\AbstractModel;
use Phalcon\Mvc\Model\Authenticatable as ModelAuthenticatable;
use Phalcon\Mvc\Model\Authorizable as ModelAuthorizable;

class User extends AbstractModel implements Authenticatable, Authorizable
{
	use ModelAuthenticatable, ModelAuthorizable;
}