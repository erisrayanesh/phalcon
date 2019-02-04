<?php

namespace Phalcon\Events;

trait EventsAware
{
	/**
	 * Sets events manager
	 * @var ManagerInterface
	 */
	protected $eventsManager;

	public function setEventsManager(ManagerInterface $eventsManager)
	{
		$this->eventsManager = $eventsManager;
	}

	/**
	 * Returns attached events manager
	 * @return ManagerInterface
	 */
	public function getEventsManager()
	{
		return $this->eventsManager;
	}

	/**
	 * @return bool
	 */
	public function hasEventsManager()
	{
		return $this->eventsManager != null;
	}

}