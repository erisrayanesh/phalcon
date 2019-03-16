<?php

namespace Phalcon\Auth\Drivers;

use Phalcon\Auth\Authenticatable;
use Phalcon\Auth\UserProvider;
use Phalcon\Events\EventsAware;
use Phalcon\Events\EventsAwareInterface;

class Session implements StatefulDriver, EventsAwareInterface
{

	use DriverHelpers, EventsAware;

	protected $name = "";

	protected $loggedOut = false;

	protected $remembered = false;

	/**
	 * The user we last attempted to retrieve.
	 *
	 * @var \Phalcon\Auth\Authenticatable
	 */
	protected $lastAttempted;

	/**
	 * Session constructor.
	 * @param $name
	 * @param UserProvider $resolver
	 */
	public function __construct($name, UserProvider $resolver)
	{
		$this->name = $name;
		$this->userResolver = $resolver;
	}

	public function attempt(array $credentials = [], $remember = false)
	{
		$this->fireAttemptEvent($credentials, $remember);

		$this->lastAttempted = $user = $this->getUserResolver()->findByCredentials($credentials);

		if ($this->hasValidCredentials($user, $credentials)) {
			$this->login($user, $remember);
			return true;
		}

		$this->fireFailedEvent($user, $credentials);

		return false;
	}

	public function once(array $credentials = [])
	{
		$this->fireAttemptEvent($credentials);

		if ($this->validate($credentials)) {
			$this->setUser($this->lastAttempted);
			return true;
		}

		return false;
	}

	public function login(Authenticatable $user, $remember = false)
	{
		$this->updateSession($user->getAuthIdentifier());

		if ($remember) {
			if (empty($user->getRememberToken())) {
				$this->cycleRememberToken($user);
			}
			$this->storeRememberCookie($user);
		}

		$this->fireLoginEvent($user, $remember);

		$this->setUser($user);
	}

	public function loginUsingId($id, $remember = false)
	{
		if (!is_null($user = $this->getUserResolver()->findById($id))) {
			$this->login($user, $remember);
			return $user;
		}

		return false;
	}

	public function onceUsingId($id)
	{
		if (!is_null($user = $this->getUserResolver()->findById($id))) {
			$this->setUser($user);
			return $user;
		}

		return false;
	}

	public function isRemembered()
	{
		return $this->remembered;
	}

	public function logout()
	{
		$user = $this->user();

		$this->clearUserDataFromStorage();

		if (!is_null($this->user)) {
			$this->cycleRememberToken($user);
		}

		$this->fireLogoutEvent($user);

		$this->user = null;
		$this->loggedOut = true;
	}

	public function user()
	{
		if ($this->loggedOut) {
			return;
		}

		if (!is_null($this->user)) {
			return $this->user;
		}

		$id = session()->get($this->getName());

		if (!is_null($id) && $this->user = $this->userResolver->findById($id)) {
			$this->fireAuthenticatedEvent($this->user);
		}

		if (is_null($this->user)) {

			$this->user = $this->rememberUser();

			if ($this->user) {
				$this->updateSession($this->user->getAuthIdentifier());
				$this->fireLoginEvent($this->user, true);
			}
		}

		return $this->user;
	}

	public function id()
	{
		if ($this->loggedOut) {
			return;
		}

		return $this->user()
			? $this->user()->getAuthIdentifier()
			: session()->get($this->getName());
	}

	public function validate(array $credentials = [])
	{
		$this->lastAttempted = $user = $this->getUserResolver()->findByCredentials($credentials);
		return $this->hasValidCredentials($user, $credentials);
	}

	/**
	 * Return the currently cached user.
	 *
	 * @return \Phalcon\Auth\Authenticatable|null
	 */
	public function getUser()
	{
		return $this->user;
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

		$this->loggedOut = false;

		$this->fireAuthenticatedEvent($user);

		return $this;
	}

	//========== HELPERS ================

	public function getName()
	{
		return 'login_'.$this->name.'_'.sha1(static::class);
	}

	public function getLastAttempted()
	{
		return $this->lastAttempted;
	}

	protected function getRememberCookie()
	{
		$rmu = cookie()->get($this->getRememberCookieName());
		if (!empty($rmu) && !empty($rmu->getValue())){
			return $rmu;
		}
		return null;
	}

	protected function rememberUser()
	{
		$rmu = $this->getRememberCookie();

		if (empty($rmu)){
			return null;
		}

		if (!strpos($rmu->getValue(), "|")){
			return null;
		}

		list($id, $token) = explode("|", $rmu->getValue());
		$this->remembered = !is_null($user = $this->getUserResolver()->findByToken($id, $token));

		return $user;
	}

	protected function cycleRememberToken(Authenticatable $user)
	{
		$user->setRememberToken($token = getRandom()->base58(60));
		$this->getUserResolver()->updateRememberToken($user, $token);
	}

	protected function storeRememberCookie(Authenticatable $user)
	{
		cookie()->set($this->getRememberCookieName(), $user->getAuthIdentifier() . "|" . $user->getRememberToken(), time() + (10 * 365 * 24 * 60 * 60));
	}

	public function getRememberCookieName()
	{
		return 'remember_'.$this->name.'_'.sha1(static::class);
	}

	protected function clearUserDataFromStorage()
	{
		session()->remove($this->getName());

		if (!empty($this->getRememberCookie())) {
			cookie()->forget($this->getRememberCookieName());
		}
	}

	protected function updateSession($id)
	{
		session()->set($this->getName(), $id);
	}

	protected function hasValidCredentials($user, $credentials)
	{
		return !empty($user) && $this->getUserResolver()->validateCredentials($user, $credentials);
	}

	protected function fireAuthenticatedEvent($user)
	{
		if ($this->hasEventsManager()) {
			$this->getEventsManager()->fire("user:authenticated", $this, $this->name, $user);
		}
	}

	protected function fireLoginEvent($user, $remember = false)
	{
		if ($this->hasEventsManager()) {
			$this->getEventsManager()->fire("user:login", $this, $this->name, $user, $remember);
		}
	}

	protected function fireAttemptEvent(array $credentials, $remember = false)
	{
		if ($this->hasEventsManager()) {
			$this->getEventsManager()->fire("user:attempting", $this, $this->name, $credentials, $remember);
		}
	}

	protected function fireFailedEvent($user, array $credentials)
	{
		if ($this->hasEventsManager()) {
			$this->getEventsManager()->fire("user:failed", $this, $this->name, $user, $credentials);
		}
	}

	protected function fireLogoutEvent($user)
	{
		if ($this->hasEventsManager()) {
			$this->getEventsManager()->fire("user:logout", $this, $this->name, $user);
		}
	}

}