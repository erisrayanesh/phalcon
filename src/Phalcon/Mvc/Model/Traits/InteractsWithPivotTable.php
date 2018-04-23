<?php

namespace Phalcon\Mvc\Model\Traits;


use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Relation;

trait InteractsWithPivotTable
{

	public function attach($relationAlias, $ids, array $attributes = [], $touch = true)
	{
		$relationship = $this->getRelation($relationAlias);

		$intModel = $relationship->getIntermediateModel();
		$intField = $this->castFieldToString($relationship->getIntermediateFields());
		$intRefField = $this->castFieldToString($relationship->getIntermediateReferencedFields());
		$primaryKeyValue = $this->getPrimaryKeyValue($relationship);

		$ids = $this->parseIds($ids);

		if (!is_array($ids)){
			$ids = [$ids];
		}

		foreach ($ids as $id){

			$data = array_merge($attributes, [
				$intField    => $primaryKeyValue,
				$intRefField => $id
			]);

			$m = new $intModel($data);
			$m->save();

			/*if ($m->save() === false) {
				//TODO: Needs discussion
				continue;
            }*/
		}

	}

	public function detach($relationAlias, $ids = null, $touch = true)
	{
		$relationship = $this->getRelation($relationAlias);
		$query = $this->newPivotQuery($relationship);

		if (!is_null($ids)) {

			$ids = $this->parseIds($ids);

			if (!is_array($ids)){
				$ids = [$ids];
			}

			if (empty($ids)) {
				return 0;
			}

			$query->inWhere($this->castFieldToString($relationship->getIntermediateReferencedFields()), $ids);

		}

		// Once we have all of the conditions set on the statement, we are ready
		// to run the delete on the pivot table. Then, if the touch parameter
		// is true, we will go ahead and touch all related models to sync.
		$results = $query->execute()->delete();

//		if ($touch) {
//			$this->touchIfTouching();
//		}

		return $results;
	}

	public function sync($relationAlias, $ids, $detaching = true)
	{
		$relationship = $this->getRelation($relationAlias);

		$intRefField = $this->castFieldToString($relationship->getIntermediateReferencedFields());
		$ids = $this->parseIds($ids);
		$current = array_pluck($this->newPivotQuery($relationship)->execute()->toArray(), $intRefField);

		$detach = array_diff($current, $ids);
		if ($detaching && count($detach) > 0) {
			$this->detach($relationAlias, $detach);
		}

		$attach = array_diff($ids, $current);
		$this->attach($relationAlias, $attach);

	}

	public function withPivot($relationAlias, $arguments = null)
	{

		if (!is_array($relationAlias)){
			$relationAlias = [$relationAlias];
		}

		foreach ($relationAlias as $alias){

			$relationship = $this->getRelation($alias);

			if ($relationship->getType() !== Relation::HAS_MANY_THROUGH){
				return;
			}

			$intRefField = $this->castFieldToString($relationship->getIntermediateReferencedFields());
			$refField = $this->castFieldToString($relationship->getReferencedFields());

			//add relation to model
			if (!isset($this->{$alias})){
				$this->{$alias} = $this->getRelated($alias, $arguments);
			}

			//iterate through relation models to add pivots
			foreach ($this->{$alias} as $item) {
				$model = $this->newPivotQuery($relationship)
					->andWhere($intRefField . " = :arg1:", [
						"arg1" => $item->readAttribute($refField)
					])->execute()->getFirst();
				$item->writeAttribute('pivot',  $model);
			}
		}

		return $this;
	}




}