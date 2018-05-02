<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use asinfotrack\yii2\toolbox\components\Icon;

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
	 * @var string|callable same functionality as the original implementation
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
	 * a boolean value, a callable with the signature `function ($name)`
	 * returning a boolean value or a string containing role the user needs.
	 */
	public $buttonRights = [];

	/**
	 * @var callable optional callable to create icons in a custom way. If
	 * implemented, the callback should have the signature `function ($iconName)`
	 * and return the html code of the icon.
	 */
	public $createIconCallback;

	/**
	 * @inheritdoc
	 */
	protected function initDefaultButtons()
	{
		$this->initDefaultButton('view', 'eye');
		$this->initDefaultButton('update', 'pencil');
		$this->initDefaultButton('delete', 'trash', [
			'data'=>[
				'confirm'=>Yii::t('yii', 'Are you sure you want to delete this item?'),
				'method'=>'post',
			],
		]);
	}

	/**
	 * @inheritdoc
	 */
	protected function initDefaultButton($name, $iconName, $additionalOptions=[])
	{
		//catch if defined already
		if (isset($this->buttons[$name])) {
			return;
		}

		//add callback which defines the button
		$this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
			$title = Yii::t('yii', ucfirst($name));
			$icon = $this->createIcon($iconName);
			$options = ArrayHelper::merge([
				'title'=>$title,
				'aria-label'=>$title,
				'data'=>[
					'pjax'=>'0',
					'tooltip'=>$title,
				],
			], ArrayHelper::merge($additionalOptions, $this->buttonOptions));

			return Html::a($icon, $url, $options);
		};
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		//create the final template
		$rowTemplate = is_callable($this->template) ? call_user_func($this->template, $model, $key, $index) : $this->template;

		//replace the placeholders
		return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
			$name = $matches[1];

			//check if button is defined
			if (!isset($this->buttons[$name])) return '';

			//check rights for backwards compatibility (old behavior of right check)
			if (isset($this->buttonRights[$name])) {
				$rightVal = $this->buttonRights[$name];

				if (is_callable($rightVal)) {
					if (call_user_func($rightVal, $name) === false) return '';
				} else if (is_bool($this->buttonRights[$name])) {
					if ($rightVal === false) return '';
				} else if (is_string($rightVal)) {
					if (!Yii::$app->user->can($rightVal)) return '';
				}
			}

			//new handling of yii2 visibility
			if (isset($this->visibleButtons[$name])) {
				$visibleVal = $this->visibleButtons[$name];

				if (is_callable($visibleVal)) {
					if (call_user_func($visibleVal, $model, $key, $index) === false) return '';
				} else if (is_bool($visibleVal)) {
					if ($visibleVal === false) return false;
				}
			}

			//render button
			$url = $this->createUrl($name, $model, $key, $index);
			return call_user_func($this->buttons[$name], $url, $model, $key);
		}, $rowTemplate);
	}

	/**
	 * Creates the icons as used by the buttons
	 *
	 * @param string $iconName the name of the icon to use
	 * @return string the final html code of the icon
	 */
	protected function createIcon($iconName)
	{
		if (is_callable($this->createIconCallback)) {
			return call_user_func($this->createIconCallback, $iconName);
		} else {
			return Icon::create($iconName);
		}
	}

}
