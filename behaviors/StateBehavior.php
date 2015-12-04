<?php
namespace asinfotrack\yii2\toolbox\behaviors;

use Yii;
use yii\base\InvalidCallException;
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
	 * Advances the owners state if possible
	 *
	 * @param bool $runValidation if set to true, the owner will be validated before saving
	 * @return bool true if ok, false if something went wrong
	 */
	public function checkAndAdvanceState($runValidation=true)
	{
		$transaction = Yii::$app->db->beginTransaction();

		while ($this->hasNextState()) {
			if ($this->advanceOneState(true)) {
				if (!$this->saveStateAttribute($runValidation)) {
					$transaction->rollBack();
					return false;
				}
			} else {
				break;
			}
		}

		$transaction->commit();
		return true;
	}

	/**
	 * Request the model to change into a certain state. The model will try to iterate
	 * over all the states between its current and the desired state.
	 *
	 * @param integer|string $stateValue the desired target state
	 * @param bool $runValidation if set to true, the owner will be validated before saving
	 * @return bool true upon success
	 */
	public function requestState($stateValue, $runValidation=true)
	{
		//validate state and that it doesn't have it already
		$this->stateExists($stateValue, true);
		if ($this->isInState($stateValue)) {
			$reqStateCfg = $this->getStateConfig($stateValue);
			$msg = Yii::t('The model is already in the requested state {state}', ['state'=>$reqStateCfg['label']]);
			throw new InvalidCallException($msg);
		}

		//try advancing
		$transaction = Yii::$app->db->beginTransaction();
		while (!$this->isInState($stateValue)) {
			if ($this->advanceOneState(false)) {
				if (!$this->saveStateAttribute($runValidation)) {
					$transaction->rollBack();
					return false;
				}
			} else {
				break;
			}
		}

		$transaction->commit();
		return $this->isInState($stateValue);
	}

	/**
	 * Does the actual work to advance one single state.
	 *
	 * @param bool|false $requiresCallback if set to true, the state will only be advanced
	 * if a preconditionCallback is present
	 * @return bool true upon success
	 */
	protected function advanceOneState($requiresCallback=false)
	{
		if (!$this->hasNextState()) return false;
		if (!$this->meetsStatePreconditions($this->getNextState(), $requiresCallback)) return false;

		$nextCfg = $this->getStateConfig($this->getNextState());
		$this->owner->{$this->stateAttribute} = $nextCfg['value'];

		return true;
	}

	/**
	 * Checks if the owner has a state BEFORE the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return bool
	 */
	public function isLowerThanState($stateValue)
	{
		return $this->compareState($stateValue) == -1;
	}

	/**
	 * Checks if the owner has a state BEFORE or EXACTLY the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return bool
	 */
	public function isLowerOrEqualThanState($stateValue)
	{
		$cmp = $this->compareState($stateValue);
		return $cmp == -1 || $cmp == 0;
	}

	/**
	 * Checks if the owner is EXACTLY IN the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return bool
	 */
	public function isInState($stateValue)
	{
		return $this->compareState($stateValue) == 0;
	}

	/**
	 * Checks if the owner has EXACTLY the one provided or a HIGHER one
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return bool
	 */
	public function isEqualOrHigherThanState($stateValue)
	{
		$cmp = $this->compareState($stateValue);
		return $cmp == 0 || $cmp == 1;
	}

	/**
	 * Checks if the owner has a state AFTER the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return bool
	 */
	public function isHigherThanState($stateValue)
	{
		return $this->compareState($stateValue) == 1;
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
	 * Returns whether or not a state exists in the current config
	 *
	 * @param integer|string $stateValue the actual state value
	 * @param bool $throwException if set to true an exception will be thrown when the
	 * state doesn't exist
	 * @return bool true if it exists
	 * @throws InvalidParamException if it doesn't exist and exception is desired
	 */
	public function stateExists($stateValue, $throwException=false)
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
	 * Returns the states as an array for filtering
	 *
	 * @return array
	 */
	public function getStateFilter()
	{
		return ArrayHelper::map($this->stateConfig, 'value', 'label');
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
	 * Compares the owners current state with a state provided.
	 * Following return values:
	 * -1:	the current state is BEFORE the one provided
	 *  0:	the owner is EXACTLY IN the provided state
	 *  1:	the current state is AFTER the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return int either -1, 0 or 1
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function compareState($stateValue)
	{
		//validate state
		$this->stateExists($stateValue, true);

		//get and compare current value
		$curVal = $this->owner->{$this->stateAttribute};
		if ($curVal === null) {
			return -1;
		} else if ($curVal == $stateValue) {
			return 0;
		}

		//compare
		$foundCurrent = false;
		foreach (ArrayHelper::getColumn($this->stateConfig, 'value') as $val) {
			if ($val == $stateValue) {
				return $foundCurrent ? -1 : 1;
			} else if ($val == $curVal) {
				$foundCurrent = true;
			}
		}

		//catch running over
		$msg = Yii::t('app', "The state {state} wasn't found while comparing", ['state'=>$stateValue]);
		throw new InvalidConfigException($msg);
	}

	/**
	 * Validates the state configuration
	 *
	 * @return bool true if config is ok
	 * @throws \yii\base\InvalidConfigException when config is illegal
	 */
	protected function validateStateConfig()
	{
		if (empty($this->stateConfig)) {
			$msg = Yii::t('app', 'Empty state configurations are not allowed');
			throw new InvalidConfigException($msg);
		}

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

	/**
	 * Saves the state attribute
	 *
	 * @param bool $runValidation whether or not to validate the state attribute
	 * @return bool true if successfully saved
	 */
	protected function saveStateAttribute($runValidation)
	{
		/* @var $owner \yii\db\ActiveRecord */
		$owner = $this->owner;

		//catch unchanged state attribute
		if (!$this->hasChangedStateAttribute()) return false;

		//fetch relevant configs
		$curCfg = $this->getStateConfig($owner->getOldAttribute($this->stateAttribute));
		$nextCfg = $this->getStateConfig();

		//call leave state callback on old state
		if (isset($curCfg['leaveStateCallback'])) {
			call_user_func($curCfg['leaveStateCallback'], $owner, $curCfg);
		}

		//save it
		if ($owner->save($runValidation, [$this->stateAttribute])) {
			//call enter state callback on new state
			if (isset($nextCfg['enterStateCallback'])) {
				call_user_func($nextCfg['enterStateCallback'], $owner, $nextCfg);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks whether or not the state attribute of the owner is dirty
	 *
	 * @return bool true if changed
	 */
	protected function hasChangedStateAttribute()
	{
		return isset($this->owner->dirtyAttributes[$this->stateAttribute]);
	}

}
