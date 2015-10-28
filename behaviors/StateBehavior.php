<?php
namespace asinfotrack\yii2\toolbox\behaviors;

use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * State functionality behavior for ActiveRecord-attributes. Extended documentation
 * coming soon.
 *
 * CLASS IS STILL UNDER DEVELOPMENT!
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class StateBehavior extends \yii\base\Behavior
{

	/**
	 * @var array cache to retrieve a state config by the states value
	 */
	protected $cacheConfigMap = [];

	/**
	 * @var array cache to enable fast finding of previous or next states
	 */
	protected $cacheIndexMap = [];

	/**
	 * @var string name of the column holding the state
	 */
	public $stateAttribute = 'state';

	/**
	 * @var array holds the configuration for the states. The array needs to contain
	 * an entry for each state in the correct order. Each state is defined as an array
	 * the following indexes:
	 * - value: the actual value which is persisted in the db (mandatory!). The value
	 * needs to be either an integer or a string
	 * - label: the label of the state (mandatory!)
	 * - allowBackwardsStep: whether or not to allow stepping backwards from this
	 * step (defaults to false)
	 * - preconditionCallback: an optional anonymous function with the signature
	 * 'function($model, $stateConfig)' returning a boolean value to check if the
	 * preconditions for a step are met
	 * - bsClass: an optional bootstrap class (eg success, warning, etc.)
	 * - iconName: an optional icon name which can be used with an icon-font
	 */
	public $stateConfig = [];

	/**
	 * @var bool if set to true, the corresponding method of this behavior (checkAndAdvanceState)
	 * is called automatically upon saving this record
	 */
	public $enableAutoAdvance = true;

	/**
	 * @var bool whether or not to do auto-advancing before saving instead of
	 * after saving
	 */
	public $autoAdvanceBeforeSave = false;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//validate config
		if (!$this->validateStateConfig()) {
			throw new InvalidParamException('The state configuration is not ok...please check the comment of the stateConfig field!');
		}

		//fill cache map
		foreach ($this->stateConfig as $i=>$config) {
			$this->cacheConfigMap[$config['value']] = $config;
			$this->cacheIndexMap[$config['value']] = $i;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function events()
	{
		if ($this->autoAdvanceBeforeSave) {
			return [
				ActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeInsert',
				ActiveRecord::EVENT_BEFORE_UPDATE => 'onBeforeUpdate',
			];
		} else {
			return [
				ActiveRecord::EVENT_AFTER_INSERT => 'onAfterInsert',
				ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterUpdate',
			];
		}
	}

	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		//assert owner extends class ActiveRecord
		if (!($owner instanceof ActiveRecord)) {
			throw new InvalidConfigException('StateBehavior can only be applied to classes extending \yii\db\ActiveRecord');
		}
		if ($owner->tableSchema->getColumn($this->stateAttribute) === null) {
			throw new InvalidConfigException(sprintf('The table %s does not contain a column named %s', $owner->tableName(), $this->stateAttribute));
		}

		parent::attach($owner);
	}

	/**
	 * Handles insert events
	 *
	 * @param \yii\base\ModelEvent $event
	 */
	public function onBeforeInsert($event)
	{
		if ($this->enableAutoAdvance) $this->checkAndAdvanceState();
	}

	/**
	 * Handles after insert events
	 *
	 * @param \yii\db\AfterSaveEvent $event
	 */
	public function onAfterInsert($event)
	{
		if ($this->enableAutoAdvance) $this->checkAndAdvanceState();
	}

	/**
	 * Handles update events
	 *
	 * @param \yii\base\ModelEvent $event
	 */
	public function onBeforeUpdate($event)
	{
		if ($this->enableAutoAdvance) $this->checkAndAdvanceState();
	}

	/**
	 * Handles after update events
	 *
	 * @param \yii\db\AfterSaveEvent $event
	 */
	public function onAfterUpdate($event)
	{
		if ($this->enableAutoAdvance) $this->checkAndAdvanceState();
	}

	/**
	 * Advances the owners state if possible
	 *
	 * @param bool $saveImmediately if set to true, the owner will be saved after setting new state
	 * @param bool $runValidation if set to true, the owner will be validated before saving
	 * @return bool returns the result of the saving process or true if saving was not desired
	 */
	public function checkAndAdvanceState($saveImmediately=true, $runValidation=false)
	{
		$curStateConfig = $this->cacheConfigMap[$this->owner->{$this->stateAttribute}];
		$nextStateConfig = $this->getNextState($curStateConfig['value'], true);
		if ($nextStateConfig === null) return true;

		while ($nextStateConfig !== null && $this->hasStatePreconditions($nextStateConfig['value'], true)) {
			$this->owner->{$this->stateAttribute} = $nextStateConfig['value'];
			$nextStateConfig = $this->getNextState($nextStateConfig['value'], true);
		}

		if (isset($this->owner->dirtyAttributes[$this->stateAttribute]) && $saveImmediately) {
			$this->owner->save($runValidation, [$this->stateAttribute]);
		} else {
			return true;
		}
	}

	/**
	 * Checks if an attribute has a state or not. The states are checked in
	 * sequential order
	 *
	 * @param mixed $stateToCheckValue
	 * @param bool $rightNow if set to true, the owner needs to have exaclty this state
	 * @return bool true if the
	 */
	public function isInState($stateToCheckValue, $rightNow=false)
	{
		$currentState = $this->owner->{$this->stateAttribute};
		if ($rightNow) return $currentState == $stateToCheckValue;

		foreach (ArrayHelper::getColumn($this->stateConfig, 'value') as $curValue) {
			if ($curValue == $stateToCheckValue) {
				return true;
			} else if ($curValue == $currentState) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Returns the states as an array for filtering
	 *
	 * @return array
	 */
	public function stateFilter()
	{
		return ArrayHelper::map($this->stateConfig, 'value', 'label');
	}

	/**
	 * Checks if the preconditions for a state are met
	 *
	 * @param mixed $stateValue the value of the state to check
	 * @param bool $requiresCallback if set to true and no callback is set, false is returned
	 * @return bool true if an anonymous function is set and preconditions are met
	 */
	public function hasStatePreconditions($stateValue, $requiresCallback=false)
	{
		$config = $this->getStateConfig($stateValue);
		if (isset($config['preconditionCallback'])) {
			return call_user_func($config['preconditionCallback'], $this->owner, $config);
		} else {
			return !$requiresCallback;
		}
	}

	/**
	 *
	 *
	 * @param $stateValue
	 * @return array config of the state
	 * @throws InvalidParamException if a state doesn't exist
	 */
	public function getStateConfig($stateValue)
	{
		$this->stateExists($stateValue, true);
		return $this->cacheConfigMap[$stateValue];
	}

	/**
	 * Returns the next states config or value if there is one. If the current state
	 * is the last step, null is returned.
	 *
	 * @param $currentStateValue the value to get the next state for
	 * @param bool $configInsteadOfValue if set to true, the config of the next state is returned
	 * @return mixed|array|null either the next states value / config or null if no next state
	 */
	public function getNextState($currentStateValue, $configInsteadOfValue=false)
	{
		$this->stateExists($currentStateValue, true);
		$nextIndex = $this->cacheIndexMap[$currentStateValue] + 1;

		if (isset($this->stateConfig[$nextIndex])) {
			$nextConfig = $this->stateConfig[$nextIndex];
			return $configInsteadOfValue ? $nextConfig : $nextConfig['value'];
		} else {
			return null;
		}
	}

	/**
	 * Returns whether or not a state exists in the current config
	 *
	 * @param $stateValue the actual state value
	 * @param bool $throwException if set to true an exception will be thrown when the
	 * state doesn't exist
	 * @return bool true if it exists
	 * @throws InvalidParamException if it doesn't exist and exception is desired
	 */
	protected function stateExists($stateValue, $throwException=false)
	{
		if (isset($this->cacheConfigMap[$stateValue])) {
			return true;
		} else if ($throwException) {
			throw new InvalidParamException(sprintf('There is no state %s defined', $stateValue));
		} else {
			return false;
		}
	}

	/**
	 * Validates the state configuration
	 *
	 * @return bool true if config is ok
	 */
	protected function validateStateConfig()
	{
		if (empty($this->stateConfig)) return false;

		$config = &$this->stateConfig;

		foreach ($config as $i=>$state) {
			if (empty($state['value']) || empty($state['label'])) {
				return false;
			}

			if (!is_int($state['value']) && !is_string($state['value'])) {
				return false;
			}

			if (!isset($state['allowBackwardsStep'])) {
				$state['allowBackwardsStep'] = false;
			}

			if (isset($state['preconditionCallback']) && !($state['preconditionCallback'] instanceof \Closure)) {
				return false;
			}

			if (!isset($state['groups'])) {
				$state['groups'] = [];
			} else {
				foreach ($state['groups'] as &$group) {
					if (!is_string($group)) return false;
					$group = strtolower($group);
				}
			}
		}

		return true;
	}

}
