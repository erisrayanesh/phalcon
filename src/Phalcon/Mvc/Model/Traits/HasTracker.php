<?php

namespace Phalcon\Mvc\Model\Traits;


use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Mvc\Model\Behavior\Timestampable;

trait HasTracker
{

//    ALTER TABLE `accompanies`
//    ADD `created_at` varchar(19) NULL DEFAULT NULL,
//    ADD `created_by` int(10) UNSIGNED NOT NULL,
//    ADD `updated_at` varchar(19) NULL DEFAULT NULL,
//    ADD `updated_by` int(10) UNSIGNED NOT NULL,
//    ADD `deleted_at` varchar(19) NULL DEFAULT NULL,
//    ADD `deleted_by` int(10) UNSIGNED DEFAULT NULL

	//ALTER TABLE `projects` ADD `created_by` INT UNSIGNED NOT NULL AFTER `created_at`, ADD `updated_by` INT UNSIGNED NOT NULL AFTER `updated_at`, ADD `deleted_by` INT UNSIGNED NOT NULL AFTER `deleted_at`;
	//ALTER TABLE `projects` ADD FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE, ADD FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

	public $tracker = true;

	public $created_by_field = "created_by";

	public $updated_by_field = "updated_by";

	public $deleted_by_field = null;

	public function initHasTracker()
	{

		if (!isset($this->tracker) || !$this->tracker){
			return;
		}

		$behavior = [];

		if ($this->getCreatedByField() !== null){
			$behavior['beforeValidationOnCreate'] = [
				'field'  => $this->getCreatedByField(),
				'generator' => function () {
					return $this->getUserID();
				}
			];
		}


		if ($this->getUpdatedByField() !== null){
			$behavior['beforeValidation'] = [
				'field'  => $this->getUpdatedByField(),
				'generator' => function () {
					return $this->getUserID();
				}
			];
		}

		if (!empty($behavior)) {
			$this->addBehavior(new Timestampable($behavior));
		}

		if ($this->isSoftDeleteTrackerEnabled()){
			$this->addBehavior(
				new SoftDelete(
					[
						'field' => $this->getDeletedByField(),
						'value' => $this->getUserID(),
					]
				)
			);
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCreatedByField()
	{
		return $this->created_by_field;
	}

	/**
	 * @param $created_by_field
	 * @return $this
	 */
	public function setCreatedByField($created_by_field)
	{
		$this->created_by_field = $created_by_field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUpdatedByField()
	{
		return $this->updated_by_field;
	}

	/**
	 * @param $updated_by_field
	 * @return $this
	 */
	public function setUpdatedByField($updated_by_field)
	{
		$this->updated_by_field = $updated_by_field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDeletedByField()
	{
		return $this->deleted_by_field;
	}

	/**
	 * @param $deleted_by_field
	 * @return $this
	 */
	public function setDeletedByField($deleted_by_field)
	{
		$this->deleted_by_field = $deleted_by_field;
		return $this;
	}

	public function getUserID()
	{
		return auth()->check()? auth()->user()->id : 0;
	}

	public function enableSoftDeleteTracker($field = 'deleted_by')
	{
		$this->setDeletedByField($field ?: 'deleted_by');
		return $this;
	}

	public function isSoftDeleteTrackerEnabled()
	{
		return $this->getDeletedByField() !== null;
	}



}