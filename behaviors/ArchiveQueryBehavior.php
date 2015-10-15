<?php
namespace asinfotrack\yii2\toolbox\behaviors;

/**
 * Behavior for the ActiveQuery-implementation of a model using
 * the ArchiveBehavior. It extends the query with methods for working
 * with the ArchiveBehavior and filtering the models by their archived-
 * state.
 * 
 * @property \yii\db\ActiveQuery $owner
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ArchiveQueryBehavior extends \yii\base\Behavior
{

	/**
	 * @var \asinfotrack\yii2\toolbox\behaviors\ArchiveBehavior
	 */
	private $modelInstance;
	
	/**
	 * Named scope to filter either archived or unarchived records
	 * 
	 * @param boolean $isArchived if set to true, only archived records will be
	 * returned. other wise only unarchived records.
	 * @return \yii\db\ActiveQuery
	 */
	public function archived($isArchived)
	{
		if ($this->modelInstance === null) {
			$this->modelInstance = new $this->owner->modelClass();
		}
		
		$value = $isArchived ? $this->modelInstance->archivedValue : $this->modelInstance->unarchivedValue;
		$this->owner->andWhere([$this->modelInstance->archiveAttribute=>$value]);
		return $this->owner;
	}
	
}
