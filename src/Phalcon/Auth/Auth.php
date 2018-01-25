<?php
namespace Phalcon\Auth;

use Phalcon\Mvc\User\Component;

class Auth extends Component
{

	protected $sessionKey = '__auth.identity';

	/**
	 * @var Authenticatable
	 */
	protected $user;

	/**
	 * Auth constructor.
	 * @param string $sessionKey
	 */
	public function __construct($sessionKey = "__auth.identity")
	{
		$this->setSessionKey($sessionKey);
	}

	public function login(Authenticatable $user)
	{
		$this->setSessionIdentity($user->getAuthenticatableIdentity(), $user->getAuthenticatableName());
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

	//=======================

	public function init(Authenticatable $class)
	{
		$identity = $this->session->get($this->getSessionKey());

		if (empty($identity)) {
			return;
		}

		if (!isset($identity['id'])) {
			return;
		}

		$user = $class->findAuthenticatable($identity['id']);
		if ($user == false) {
			return;
		}

		$this->user = $user;
	}

}
