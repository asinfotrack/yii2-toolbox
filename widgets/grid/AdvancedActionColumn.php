<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Advanced action column with functionality to show buttons depending on a
 * users role and has dynamic templates per row.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AdvancedActionColumn extends \yii\grid\ActionColumn
{

	/**
	 * @var string|\Closure the value of the initial template variable to
	 * enable dynamic templates on a per row basis
	 */
	protected $templateInternal;

	/**
	 * @var string|\Closure same functionality as the original implementation
	 * of `ActionColumn`-class, but with the possibility to set a closure instead
	 * of a fixed string. This way the template will be evaluated for each row.
	 *
	 * The callback needs to have its signature as follows:
	 * `function ($model, $key, $index)`
	 */
	public $template = '{view} {update} {delete}';

	/**
	 * @var array holding the right-configuration for each button.
	 * The array is indexed by the button-names and the values can either be
	 * a boolean value, a closure with the signature `function ($name)`
	 * returning a boolean value or a string containing role the user needs.
	 */
	public $buttonRights = [];

	/**
	 * @inheritdoc
	 *
	 * @throws \yii\base\InvalidConfigException on invalid right config
	 */
	public function init()
	{
		parent::init();

		//copy template value to internal var
		if ($this->template instanceof \Closure) {
			$this->templateInternal = $this->template;
		}

		//iterate over rights
		foreach ($this->buttonRights as $name=>$value) {
			if (!isset($this->buttons[$name])) continue;

			if (!$this->checkRight($name, $value)) {
				unset($this->buttons[$name]);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function initDefaultButton($name, $iconName, $additionalOptions = [])
	{
		if ($this->template instanceof \Closure) {
			return null;
		} else {
			parent::initDefaultButton($name, $iconName, $additionalOptions = []);
		}
	}

	/**
	 * Checks if a button should be displayed or not, depending on the defined
	 * right configuration for a button.
	 *
	 * @param string $name name of the button (eg view)
	 * @param string|\Closure|bool $value the method to check the rights
	 * @return bool the result
	 * @throws \yii\base\InvalidConfigException if $value is not in an allowed format
	 */
	protected function checkRight($name, $value)
	{
		if ($value instanceof \Closure) {
			return call_user_func($value, $name);
		} else if (is_bool($value)) {
			return $value;
		} else if (is_string($value)) {
			return Yii::$app->user->can($value);
		} else {
			$msg = Yii::t('app', 'Only string, closures or booleans allowed');
			throw new InvalidConfigException($msg);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		//update the value of the template string for the current row
		if ($this->templateInternal !== null) {
			$this->template = call_user_func($this->templateInternal, $model, $key, $index);
		}

		return parent::renderDataCellContent($model, $key, $index);
	}


}
