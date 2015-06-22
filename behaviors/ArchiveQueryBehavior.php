<?php
namespace asinfotrack\yii2\toolbox\behaviors;

/**
 * Behavior for the ActiveQuery-implementation of a model using
 * the ArchiveBehavior. It extends the query with methods for working
 * with the ArchiveBehavior and filtering the models by their archived-
 * state.
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
	public function isArchived($isArchived)
	{
		if ($this->modelInstance === null) {
			$this->modelInstance = new $this->owner->modelClass();
		}
		
		$value = $isArchived ? $this->modelInstance->archivedValue : $this->modelInstance->unarchivedValue;
		$this->andWhere([$this->modelInstance->archiveAttribute=>$value]);
		return $this->owner;
	}
	
	/**
	 * Named scope to fetch only archived records
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function archived()
	{
		return $this->isArchived(true);
	}
	
	/**
	 * Named scope to fetch only unarchived records
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function unarchived()
	{
		return $this->isArchived(false);
	}
	
}
