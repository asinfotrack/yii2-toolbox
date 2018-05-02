<?php
namespace asinfotrack\yii2\toolbox\widgets;

use Yii;
use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use asinfotrack\yii2\toolbox\components\Icon;

/**
 * This widget extends the button widget provided by yii2. It adds
 * functionality to specify an icon.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Button extends \yii\bootstrap\Button
{

	/**
	 * @var string icon-name as used in the icon library used
	 * @see \asinfotrack\yii2\toolbox\components\Icon
	 */
	public $icon;

	/**
	 * @var callable optional callable to create icons in a custom way. If
	 * implemented, the callback should have the signature `function ($iconName)`
	 * and return the html code of the icon.
	 */
	public $createIconCallback;

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$this->registerPlugin('button');
		return Html::tag($this->tagName, $this->createLabel(), $this->options);
	}

	/**
	 * Creates the label for a button
	 *
	 * @return string the label
	 */
	protected function createLabel()
	{
		$icon = empty($this->icon) ? '' : $this->createIcon($this->icon);
		if (empty($this->label) || strcmp($this->label, 'Button') === 0) {
			$label = '';
		} else {
			$label = Html::tag('span', $this->encodeLabel ? Html::encode($this->label) : $this->label);
		}

		return $icon . $label;
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
