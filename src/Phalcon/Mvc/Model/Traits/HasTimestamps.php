<?php

namespace Phalcon\Mvc\Model\Traits;


use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

trait HasTimestamps
{
	public $created_at_field = "created_at";

	public $updated_at_field = "updated_at";

	public $deleted_at_field = null;

	public $timestamps_timezone = "Europe/London";

	public $timestamps_format = "Y-m-d H:i:s";


	public function initHasTimestamps()
	{
		$behavior = [];

		if ($this->getCreatedAtField() !== null){
			$behavior['beforeValidationOnCreate'] = [
				'field'  => $this->getCreatedAtField(),
				'generator' => function () {
					return $this->generateTimestamp();
				}
			];
		}


		if ($this->getUpdatedAtField() !== null){
			$behavior['beforeValidation'] = [
				'field'  => $this->getUpdatedAtField(),
				'generator' => function () {
					return $this->generateTimestamp();
				}
			];
		}

		$this->addBehavior(
			new Timestampable($behavior)
		);

		if ($this->isSoftDeleteTimestampEnabled()){
			$this->addBehavior(
				new SoftDelete(
					[
						'field' => $this->getDeletedAtField(),
						'value' => $this->generateTimestamp(),
					]
				)
			);
		}

        return $this;

	}


	/**
	 * @return string
	 */
	public function getCreatedAtField()
	{
		return $this->created_at_field;
	}

	/**
	 * @param string $created_at_field
	 */
	public function setCreatedAtField($created_at_field)
	{
		$this->created_at_field = $created_at_field;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getUpdatedAtField()
	{
		return $this->updated_at_field;
	}

	/**
	 * @param string $updated_at_field
	 */
	public function setUpdatedAtField($updated_at_field)
	{
		$this->updated_at_field = $updated_at_field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDeletedAtField()
	{
		return $this->deleted_at_field;
	}

	/**
	 * @param string $deleted_at_field
	 */
	public function setDeletedAtField($deleted_at_field)
	{
		$this->deleted_at_field = $deleted_at_field;
        return $this;
	}

	public function enableSoftDeleteTimestamp($field = 'deleted_at')
	{
		$this->setDeletedAtField($field ?: 'deleted_at');
        return $this;
	}

	public function isSoftDeleteTimestampEnabled()
	{
		$this->getDeletedAtField() !== null;
	}


	public function withoutTrashed()
    {
        return " and {$this->getDeletedAtField()} = null";
    }

	/**
	 * @return string
	 */
	public function getTimestampsTimezone()
	{
		return $this->timestamps_timezone;
	}

	/**
	 * @param string $timestamps_timezone
	 */
	public function setTimestampsTimezone($timestamps_timezone)
	{
		$this->timestamps_timezone = $timestamps_timezone;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getTimestampsFormat()
	{
		return $this->timestamps_format;
	}

	/**
	 * @param string $timestamps_format
	 */
	public function setTimestampsFormat($timestamps_format)
	{
		$this->timestamps_format = $timestamps_format;
        return $this;
	}

	//================================================

	final protected function generateTimestamp()
	{
		return $this->createFreshTimestamp() ?: $this->generateDefaultTimestamp();
	}

	final protected function generateDefaultTimestamp()
	{
		$datetime = new \Datetime('now', new \DateTimeZone($this->getTimestampsTimezone()));
		return $datetime->format($this->getTimestampsFormat());
	}

	public function createFreshTimestamp()
	{
		return null;
	}
}