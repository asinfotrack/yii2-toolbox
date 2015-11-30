<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * Helper class to work with component-configurations
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ComponentConfigHelper
{

	/**
	 * Checks whether or not a subject has a behavior of a certain type attached.
	 *
	 * @param \yii\base\Component $subject the subject to check
	 * @param string|\yii\base\Behavior $behavior either the class name or an instance of the behavior
	 * @param bool $throwException if set to true, an exception will be thrown if the
	 * subject doesn't have the behavior attached
	 * @return bool true if attached
	 * @throws \yii\base\InvalidParamException when subject is of wrong type
	 * @throws \yii\base\InvalidConfigException if desired and behavior missing
	 */
	public static function hasBehavior($subject, $behavior, $throwException=false)
	{
		//only components allowed
		if (!$subject instanceof \yii\base\Component) {
			throw new InvalidParamException(Yii::t('app', 'Subject must extend Component'));
		}

		//prepare vars
		$behavior = $behavior instanceof \yii\base\Behavior ? $behavior::className() : $behavior;

		//check if behavior is attached
		$found = false;
		foreach ($subject->behaviors() as $name=>$config) {
			$className = is_array($config) ? $config['class'] : $config;
			if (strcmp($className, $behavior) === 0) {
				$found = true;
				break;
			}
		}

		if ($throwException && !$found) {
			$msg = Yii::t('app', '{subject} needs to have behavior {behavior} attached', [
				'subject'=>$subject->className(),
				'behavior'=>$behavior,
			]);
			throw new InvalidConfigException($msg);
		}

		return $found;
	}

	/**
	 * Checks whether or not an object is of type active record
	 *
	 * @param mixed $object the object to check
	 * @param bool $throwException whether or not to throw an exception
	 * @return bool true if of type active record
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function isActiveRecord($object, $throwException=false)
	{
		if ($object instanceof \yii\db\ActiveRecord) return true;
		if ($throwException) {
			$msg = Yii::t('app', 'Object is not of type ActiveRecord');
			throw new InvalidConfigException($msg);
		}

		return false;
	}

}
