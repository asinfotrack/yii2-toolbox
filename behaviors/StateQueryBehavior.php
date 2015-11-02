<?php
namespace asinfotrack\yii2\toolbox\behaviors;

use yii\helpers\ArrayHelper;

/**
 * Behavior for the ActiveQuery-implementation of a model using
 * the StateBehavior. It extends the query with methods for working
 * with the StateBehavior and filtering the models by their states.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 *
 * @property \yii\db\ActiveQuery $owner
 */
class StateQueryBehavior extends \yii\base\Behavior
{

	const STATES_LOWER 				= 0;
	const STATES_LOWER_OR_EQUAL 	= 1;
	const STATES_EQUAL_OR_HIGHER	= 2;
	const STATES_HIGHER				= 3;

	/**
	 * @var \yii\db\ActiveRecord|\asinfotrack\yii2\toolbox\behaviors\StateBehavior
	 * instance of the active record implementing the state behavior
	 */
	protected $modelInstance;

	/**
	 * @var string the attribute name where the state is persisted
	 */
	protected $stateAttribute;

	/**
	 * @var array the state config as seen in the StateBehavior
	 * @see \asinfotrack\yii2\toolbox\behaviors\StateBehavior
	 */
	protected $stateConfig;

	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		parent::attach($owner);

		//get config from instance
		if ($this->stateConfig === null || $this->stateAttribute === null) {
			$this->modelInstance = new $this->owner->modelClass();
			$this->stateAttribute = $this->modelInstance->stateAttribute;
			$this->stateConfig = $this->modelInstance->stateConfig;
		}
	}

	/**
	 * Named scope to fetch all models having a state lower than the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return \asinfotrack\yii2\toolbox\behaviors\StateQueryBehavior
	 */
	public function lowerThanState($stateValue)
	{
		//validate state
		$this->modelInstance->stateExists($stateValue, true);
		//apply to query
		$this->owner->andWhere(['IN', $this->stateAttribute, $this->getStateSelection($stateValue, static::STATES_LOWER)]);
		return $this;
	}

	/**
	 * Named scope to fetch all models having a state lower or equal than the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return \asinfotrack\yii2\toolbox\behaviors\StateQueryBehavior
	 */
	public function lowerOrEqualThanState($stateValue)
	{
		//validate state
		$this->modelInstance->stateExists($stateValue, true);
		//apply to query
		$this->owner->andWhere(['IN', $this->stateAttribute, $this->getStateSelection($stateValue, static::STATES_LOWER_OR_EQUAL)]);
		return $this;
	}

	/**
	 * Named scope to filter models exactly in the provided state. Its also possible
	 * to provide an array of states. In this case all models with any of those states
	 * will be returned
	 *
	 * @param integer|string|integer[]|string[] $stateValue the value(s) of the state to check
	 * @return \asinfotrack\yii2\toolbox\behaviors\StateQueryBehavior
	 */
	public function inState($stateValue)
	{
		if (is_array($stateValue)) {
			//validate states
			foreach ($stateValue as $sv) {
				$this->modelInstance->stateExists($sv, true);
			}
			//apply to query
			$this->owner->andWhere(['IN', $this->stateAttribute, $stateValue]);
		} else {
			//validate state
			$this->modelInstance->stateExists($stateValue, true);
			//apply to query
			$this->owner->andWhere([$this->stateAttribute=>$stateValue]);
		}

		return $this;
	}

	/**
	 * Named scope to fetch all models having a state higher or equal than the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return \asinfotrack\yii2\toolbox\behaviors\StateQueryBehavior
	 */
	public function equalOrHigherThanState($stateValue)
	{
		//validate state
		$this->modelInstance->stateExists($stateValue, true);
		//apply to query
		$this->owner->andWhere(['IN', $this->stateAttribute, $this->getStateSelection($stateValue, static::STATES_EQUAL_OR_HIGHER)]);
		return $this;
	}

	/**
	 * Named scope to fetch all models having a state higher than the one provided
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @return \asinfotrack\yii2\toolbox\behaviors\StateQueryBehavior
	 */
	public function higherThanState($stateValue)
	{
		//validate state
		$this->modelInstance->stateExists($stateValue, true);
		//apply to query
		$this->owner->andWhere(['IN', $this->stateAttribute, $this->getStateSelection($stateValue, static::STATES_HIGHER)]);
		return $this;
	}

	/**
	 * Gets a selection of states from the current state config
	 *
	 * @param integer|string $stateValue the value of the state to check
	 * @param integer $which selection constant of this class
	 * @return integer[]|string[] array the desired states
	 */
	protected function getStateSelection($stateValue, $which)
	{
		$ret = [];

		$foundCurrent = false;
		foreach (ArrayHelper::getColumn($this->stateConfig, 'value') as $val) {
			if ($stateValue == $val) {
				$foundCurrent = true;
				if ($which == static::STATES_LOWER) {
					break;
				} else if ($which == static::STATES_LOWER_OR_EQUAL || $which == static::STATES_EQUAL_OR_HIGHER) {
					$ret[] = $val;
					if ($which == static::STATES_LOWER_OR_EQUAL) {
						break;
					} else {
						continue;
					}
				} else if ($which == static::STATES_HIGHER) {
					continue;
				}
			} else {
				if (!$foundCurrent && ($which == static::STATES_EQUAL_OR_HIGHER || $which == static::STATES_HIGHER)) {
					continue;
				}
				$ret[] = $val;
			}
		}

		return $ret;
	}

}
