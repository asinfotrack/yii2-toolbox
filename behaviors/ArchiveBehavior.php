<?php
namespace asinfotrack\yii2\toolbox\behaviors;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * The ArchiveBehavior adds the possibility to archive and unarchive records. This
 * is done with a column containing customizable values marking a record as either
 * archived or unarchived.
 * To use the behavior simply attach it to your model and specify the column holding
 * the archive-value.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ArchiveBehavior extends \yii\base\Behavior
{
	
	/**
	 * @var string name of the column holding the boolean flag to determine
	 * if a record is archived or not.
	 */
	public $archiveAttribute = 'is_archived';
	
	/**
	 * @var mixed|null holds the default value which will be set upon creation
	 * of a record. If set to null, no value is set.
	 */
	public $defaultArchiveValue = 0;
	
	/**
	 * Holds the value marking a record as archived (defaults to 1)
	 */
	public $archivedValue = 1;
	
	/**
	 * Holds the value marking a record as NOT archived (defaults to 0)
	 */
	public $unarchivedValue = 0;
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Behavior::events()
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_BEFORE_INSERT=>'beforeInsert',
		];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Behavior::attach()
	 */
	public function attach($owner)
	{
		//assert owner extends class ActiveRecord
		if (!($owner instanceof ActiveRecord)) {
			throw new InvalidConfigException('ArchiveBehavior can only be applied to classes extending \yii\db\ActiveRecord');
		}
		if ($owner->tableSchema->getColumn($this->archiveAttribute) === null) {
			throw new InvalidConfigException(sprintf('The table %s does not contain a column named %s', $owner->tableName(), $this->archiveAttribute));
		}
	
		parent::attach($owner);
	}
	
	/**
	 * This method is called before a record is inserted. It sets the column to
	 * the default value if there is one specified
	 */
	protected function beforeInsert()
	{
		if ($this->defaultArchiveValue === null) return;
		$this->owner->{$this->archiveAttribute} = $this->defaultArchiveValue;
	}
	
	/**
	 * Archives a record. If the second param is set to true (default),
	 * the model will be saved immediately.
	 * 
	 * @param boolean $saveImmediately if set to true, the model will be saved
	 * immediately (defaults to true)
	 * @return boolean true if archiving was successful (if $saveImmediately was
	 * set to true, the result of save() is returned)
	 */
	public function archive($saveImmediately=true)
	{
		$this->owner->{$this->archiveAttribute} = $this->archivedValue;
		return $saveImmediately ? $this->owner->save() : true;
	}
	
	/**
	 * Unarchives a record. If the second param is set to true (default),
	 * the model will be saved immediately.
	 * 
	 * @param boolean $saveImmediately if set to true, the model will be saved
	 * immediately (defaults to true)
	 * @return boolean true if archiving was successful (if $saveImmediately was
	 * set to true, the result of save() is returned)
	 */
	public function unarchive($saveImmediately=true)
	{
		$this->owner->{$this->archiveAttribute} = $this->unarchivedValue;
		return $saveImmediately ? $this->owner->save() : true;
	}
	
	/**
	 * Simple getter to decide if a record is archived or not
	 * 
	 * @return boolean true if archived, otherwise false
	 */
	public function getIsArchived()
	{
		return $this->owner->{$this->archiveAttribute} == $this->archivedValue;
	}
	
}