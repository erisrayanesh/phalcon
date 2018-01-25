<?php
namespace Phalcon\Auth;

use Phalcon\Mvc\User\Component;

class Auth extends Component
{

	protected $sessionKey = '__auth.identity';

	/**
	 * @var Authenticatable
	 */
	protected $userModel;

	protected $identityKey = 'id';

	protected $usernameKey = 'email';

	protected $passwordKey = 'password';

	/**
	 * @var Authenticatable
	 */
	protected $user;

	/**
	 * Auth constructor.
	 * @param string $sessionKey
	 * @param Authenticatable $model
	 */
	public function __construct($sessionKey = "__auth.identity")
	{
		$this->setSessionKey($sessionKey);
	}

	public function login(Authenticatable $user)
	{
		$this->setSessionIdentity($user->{$this->getIdentityKey()}, $user->getAuthName());
		$this->user = $user;
	}

	public function logout()
	{
		$this->unsetSessionIdentity();
	}

	protected function setSessionIdentity($id, $name)
	{
		$this->session->set($this->getSessionKey(), [
			'id' => $id,
			'name' => $name,
		]);
	}

	protected function unsetSessionIdentity()
	{
		$this->session->remove($this->getSessionKey());
		$this->user = null;
	}

    /**
     * Returns the current identity
     *
     * @return array
     */
    public function getSessionIdentity()
    {
        return $this->session->get($this->getSessionKey());
    }

    /**
     * Returns the current identity
     *
     * @return string
     */
    public function getSessionIdentityName()
    {
        $identity = $this->session->get($this->getSessionKey());
        return $identity['name'];
    }

    public function check()
	{
		return $this->user() !== null;
	}

    /**
     * Get the entity related to user in the active identity
     *
     * @return Authenticatable
     * @throws Exception
     */
    public function user()
    {
        return $this->user;
    }

	public function init()
	{
		$identity = $this->session->get($this->getSessionKey());

		if (empty($identity)) {
			return;
		}

		if (!isset($identity['id'])) {
			return;
		}

		$user = $this->findUserById($identity['id']);
		if ($user == false) {
			return;
		}

		$this->user = $user;
	}

	public function findUserById($id)
	{
		$instance = $this->getUserInstance();
		$method = "findFirstBy" . camelize($this->getIdentityKey());
		return $instance->{$method}($id);
	}

	public function findUserByUsername($username)
	{
		$instance = $this->getUserInstance();
		$method = "findFirstBy" . camelize($this->getUsernameKey());
		return $instance->{$method}($username);
	}

	/**
	 * @return Authenticatable
	 */
	public function getUserInstance()
	{
		$class = $this->getUserModel();
		return new $class();
	}


	//=====================

	/**
	 * @return string
	 */
	public function getSessionKey()
	{
		return $this->sessionKey;
	}

	/**
	 * @param string $sessionKey
	 * @return Auth
	 */
	public function setSessionKey($sessionKey)
	{
		$this->sessionKey = $sessionKey;

		return $this;
	}

	/**
	 * @return Authenticatable
	 */
	public function getUserModel()
	{
		return $this->userModel;
	}

	/**
	 * @param Authenticatable $userModel
	 * @return Auth
	 */
	public function setUserModel(Authenticatable $userModel)
	{
		$this->userModel = $userModel;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdentityKey()
	{
		return $this->identityKey;
	}

	/**
	 * @param string $identityKey
	 * @return Auth
	 */
	public function setIdentityKey($identityKey)
	{
		$this->identityKey = $identityKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUsernameKey()
	{
		return $this->usernameKey;
	}

	/**
	 * @param string $usernameKey
	 * @return Auth
	 */
	public function setUsernameKey($usernameKey)
	{
		$this->usernameKey = $usernameKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPasswordKey()
	{
		return $this->passwordKey;
	}

	/**
	 * @param string $passwordKey
	 * @return Auth
	 */
	public function setPasswordKey($passwordKey)
	{
		$this->passwordKey = $passwordKey;

		return $this;
	}


}
