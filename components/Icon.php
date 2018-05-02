<?php
namespace asinfotrack\yii2\toolbox\components;

use Yii;
use yii\helpers\Html;

class Icon extends \yii\base\Component
{

	protected $hasExtFa4;
	protected $hasExtFa5;

	/**
	 * @var string the component name under which the instance is configured in the
	 * yii config. If there is no instance under this name, a new one will be created
	 * for one time use only.
	 */
	public static $COMPONENT_NAME = 'icon';

	/**
	 * @var callable optional callable to create icons in a custom way. If
	 * implemented, the callback should have the signature `function ($iconName)`
	 * and return the html code of the icon.
	 */
	public $createIconCallback;

	/**
	 * @var array optional map to replace icon names with alternatives. Specify
	 * this property as an array in which the keys are the icon names to replace
	 * and the values are the names which will replace them.
	 */
	public $replaceMap = [];

	/**
	 * Shorthand method to create an icon.
	 *
	 * The method will use the singleton component instance if defined under `Yii::$app->icon` or
	 * a one time instance if not defined.
	 *
	 * @param string $iconName the desired icon name
	 * @param array $options options array for the icon
	 * @return string the icon code
	 */
	public static function create($iconName, $options=[])
	{
		if (isset(Yii::$app->{static::$COMPONENT_NAME}) && Yii::$app->{static::$COMPONENT_NAME} instanceof Icon) {
			$instance = Yii::$app->{static::$COMPONENT_NAME};
		} else {
			$instance = new Icon();
		}

		return $instance->createIcon($iconName, $options);
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->hasExtFa4 = class_exists('rmrevin\yii\fontawesome\FA');
		$this->hasExtFa5 = class_exists('rmrevin\yii\fontawesome\FAR');
	}

	/**
	 * Creates an icon with either a specified callback or by default in the following order:
	 *
	 * 1. FontAwesome 5 with rmrevin-extension in version 3.*
	 * 2. FontAwesome 4 with rmrevin-extension in version 2.*
	 * 3. yii default glyph icons
	 *
	 * @param string $iconName the desired icon name
	 * @param array $options options array for the icon
	 * @return string the icon code
	 */
	public function createIcon($iconName, $options=[])
	{
		//replace icon name if necessary
		$iconName = $this->replaceIconName($iconName);

		//create the actual icon
		if (is_callable($this->createIconCallback)) {
			return call_user_func($this->createIconCallback, $iconName, $options);
		} else {
			if ($this->hasExtFa5) {
				return call_user_func(['rmrevin\yii\fontawesome\FAR', 'icon'], $iconName, $options);
			} else if ($this->hasExtFa4) {
				return call_user_func(['rmrevin\yii\fontawesome\FA', 'icon'], $iconName, $options);
			} else {
				Html::addCssClass($options, 'glyphicon');
				Html::addCssClass($options, 'glyphicon-' . $iconName);
				return Html::tag('span', '', $options);
			}
		}
	}

	/**
	 * Replaces an old icon name with an alternative provided in the replaceMap of the class
	 *
	 * @param string $iconName the icon name to replace if necessary
	 * @return string the final icon name
	 */
	protected function replaceIconName($iconName)
	{
		return isset($this->replaceMap[$iconName]) ? $this->replaceMap[$iconName] : $iconName;
	}

}
