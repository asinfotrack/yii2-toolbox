<?php
namespace asinfotrack\yii2\toolbox\behaviors;

use Yii;
use yii\base\InvalidConfigException;
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
	 * - enterStateCallback: an optional anonymous function with the signature
	 * 'function($model, $stateConfig)' called when a state is entered
	 * - leaveStateCallback: an optional anonymous function with the signature
	 * 'function($model, $stateConfig)' called when a state is left
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
	 * @var bool whether or not to allow moving multiple steps within one save (defaults to false).
	 * This param is only relevant if enableAutoAdvance is set to true.
	 */
	public $allowMultipleStepAdvancing = false;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//validate config
		$this->validateStateConfig();

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
		if (!$this->enableAutoAdvance) return [];

		if ($this->autoAdvanceBeforeSave) {
			return [
				ActiveRecord::EVENT_BEFORE_INSERT=>'onBeforeInsert',
				ActiveRecord::EVENT_BEFORE_UPDATE=>'onBeforeUpdate',
			];
		} else {
			return [
				ActiveRecord::EVENT_AFTER_INSERT=>'onAfterInsert',
				ActiveRecord::EVENT_AFTER_UPDATE=>'onAfterUpdate',
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
			$msg = Yii::t('app', 'StateBehavior can only be applied to classes extending \yii\db\ActiveRecord');
			throw new InvalidConfigException($msg);
		}
		//assert owner has state field
		if ($owner->tableSchema->getColumn($this->stateAttribute) === null) {
			$msg = Yii::t('app', 'The table {tbl} does not contain a column named {col}', [
				'tbl'=>$owner->tableName(),
				'col'=>$this->stateAttribute,
			]);
			throw new InvalidConfigException($msg);
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
		$this->checkAndAdvanceState();
	}

	/**
	 * Handles after insert events
	 *
	 * @param \yii\db\AfterSaveEvent $event
	 */
	public function onAfterInsert($event)
	{
		$this->checkAndAdvanceState();
	}

	/**
	 * Handles update events
	 *
	 * @param \yii\base\ModelEvent $event
	 */
	public function onBeforeUpdate($event)
	{
		$this->checkAndAdvanceState();
	}

	/**
	 * Handles after update events
	 *
	 * @param \yii\db\AfterSaveEvent $event
	 */
	public function onAfterUpdate($event)
	{
		$this->checkAndAdvanceState();
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
		/* @var $owner \yii\db\ActiveRecord */
		$owner = $this->owner;

		while ($this->hasNextState() && $this->meetsStatePreconditions($this->getNextState())) {
			$curCfg = $this->getStateConfig();
			$nxtCfg = $this->getStateConfig($this->getNextState());

			if (isset($curCfg['leaveStateCallback'])) call_user_func($curCfg['leaveStateCallback'], $owner, $curCfg);
			$owner->{$this->stateAttribute} = $nxtCfg['value'];
			if (isset($nxtCfg['enterStateCallback'])) call_user_func($nxtCfg['enterStateCallback'], $owner, $curCfg);

			if (!$this->allowMultipleStepAdvancing) break;
		}

		if ($saveImmediately && isset($owner->dirtyAttributes[$this->stateAttribute])) {
			$owner->save($runValidation, [$this->stateAttribute]);
		}
	}

	/**
	 * Checks if an attribute has a state or not. The states are checked in
	 * sequential order. If an object has states A, B and C and is currently
	 * in state C:
	 * - if calling with B and setting rightNow to false (default) the method returns true
	 * - if calling with C and setting rightNow to true the method returns true
	 * - if calling with B and setting rightNow to true the method returns false
	 *
	 * @param integer|string $stateValue
	 * @param bool $rightNow if set to true, the owner needs to have exactly this state
	 * (defaults to false)
	 * @return bool true if the
	 */
	public function isInState($stateValue, $rightNow=false)
	{
		$curVal = $this->owner->{$this->stateAttribute};
		if ($rightNow) return $curVal == $stateValue;

		foreach (ArrayHelper::getColumn($this->stateConfig, 'value') as $val) {
			if ($val == $stateValue) {
				return true;
			} else if ($val == $curVal) {
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
	public function getStateFilter()
	{
		return ArrayHelper::map($this->stateConfig, 'value', 'label');
	}

	/**
	 * Checks if the preconditions for a state are met
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @param bool $requiresCallback if set to true and no callback is set, false is returned
	 * @return bool true if an anonymous function is set and preconditions are met
	 */
	public function meetsStatePreconditions($stateValue, $requiresCallback=false)
	{
		$config = $this->getStateConfig($stateValue);
		if (isset($config['preconditionCallback'])) {
			return call_user_func($config['preconditionCallback'], $this->owner, $config);
		} else {
			return !$requiresCallback;
		}
	}

	/**
	 * Gets the config for a state
	 *
	 * @param integer|string $stateValue the value to get the config for (defaults to the owners current state)
	 * @return array config of the state
	 * @throws InvalidParamException if a state doesn't exist
	 */
	public function getStateConfig($stateValue=null)
	{
		if ($stateValue === null) {
			$stateValue = $this->owner->{$this->stateAttribute};
		} else {
			$this->stateExists($stateValue, true);
		}
		return $this->cacheConfigMap[$stateValue];
	}

	/**
	 * Returns whether or not there are states after the current one
	 *
	 * @return bool true if there is a next state
	 */
	public function hasNextState()
	{
		return $this->cacheIndexMap[$this->owner->{$this->stateAttribute}] + 1 < count($this->stateConfig);
	}

	/**
	 * Returns the next states config or value if there is one. If the current state
	 * is the last step, null is returned.
	 *
	 * @param integer|string $stateValue the value to get the next state for (defaults to the owners current state)
	 * @param bool $configInsteadOfValue if set to true, the config of the next state is returned
	 * @return mixed|array|null either the next states value / config or null if no next state
	 */
	public function getNextState($stateValue=null, $configInsteadOfValue=false)
	{
		if ($stateValue === null) {
			$stateValue = $this->owner->{$this->stateAttribute};
		} else {
			$this->stateExists($stateValue, true);
		}

		if ($this->hasNextState()) {
			$nextConfig = $this->stateConfig[$this->cacheIndexMap[$stateValue] + 1];
			return $configInsteadOfValue ? $nextConfig : $nextConfig['value'];
		} else {
			return null;
		}
	}

	/**
	 * Returns whether or not a state exists in the current config
	 *
	 * @param integer|string $stateValue the actual state value
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
			$msg = Yii::t('app', 'There is no state with the value {val}', ['val'=>$stateValue]);
			throw new InvalidParamException($msg);
		} else {
			return false;
		}
	}

	/**
	 * Validates the state configuration
	 *
	 * @return bool true if config is ok
	 * @throws \yii\base\InvalidConfigException when config is illegal
	 */
	protected function validateStateConfig()
	{
		if (empty($this->stateConfig)) return false;
		$config = &$this->stateConfig;

		foreach ($config as $i=>$state) {
			//validate label and value
			if (empty($state['value']) || empty($state['label'])) {
				$msg = Yii::t('app', 'The label and the value of a state are mandatory');
				throw new InvalidConfigException($msg);
			}
			if (!is_int($state['value']) && !is_string($state['value'])) {
				$msg = Yii::t('app', 'The value must be a string or an integer ({lbl})', ['lbl'=>$state['label']]);
				throw new InvalidConfigException($msg);
			}

			//validate closures
			$callbackAttributes = ['preconditionCallback', 'enterStateCallback', 'leaveStateCallback'];
			foreach ($callbackAttributes as $cbAttr) {
				if (isset($state[$cbAttr]) && !($state[$cbAttr] instanceof \Closure)) {
					$msg = Yii::t('app', 'For {cb-attr} only closures are allowed', ['cb-attr'=>$cbAttr]);
					throw new InvalidConfigException($msg);
				}
			}

			//default settings
			if (!isset($state['allowBackwardsStep'])) $state['allowBackwardsStep'] = false;
			if (!isset($state['groups'])) $state['groups'] = [];

			//validate groups
			foreach ($state['groups'] as &$group) {
				if (!is_string($group)) {
					$msg = Yii::t('app', 'Only strings allowed for group names');
					throw new InvalidConfigException($msg);
				}
				$group = strtolower($group);
			}
		}

		return true;
	}

}
