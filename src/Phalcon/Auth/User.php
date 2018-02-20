<?php

namespace Phalcon\Auth;

use Phalcon\Mvc\AbstractModel;
use Phalcon\Mvc\Model\Traits\Authenticatable as ModelAuthenticatable;
use Phalcon\Mvc\Model\Traits\Authorizable as ModelAuthorizable;

class User extends AbstractModel implements Authenticatable, Authorizable
{
	use ModelAuthorizable, ModelAuthenticatable;
}