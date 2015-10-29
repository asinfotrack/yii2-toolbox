<?php
namespace asinfotrack\yii2\toolbox\behaviors;

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

	public function isInState($stateValue, $rightNow=false)
	{
		//validate state
		$this->modelInstance->stateExists($stateValue, true);

		//perform work
		if ($rightNow) {
			$this->owner->andWhere([$this->stateAttribute=>$stateValue]);
		} else {
			$firstState = $this->stateConfig[0]['value'];
			$possibleStates = [$firstState];

			$curState = $firstState;
			while ($curState != $stateValue) {
				$curState = $this->modelInstance->getNextState($curState);
				$possibleStates[] = $curState;
			}

			$this->owner->andWhere(['IN', $this->stateAttribute, $possibleStates]);
		}
		return $this->owner;
	}

}
