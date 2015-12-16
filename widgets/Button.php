<?php
namespace asinfotrack\yii2\toolbox\widgets;

use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

/**
 * This widget extends the button widget provided by yii2. It adds
 * functionality to specify an icon.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Button extends \yii\bootstrap\Button
{

	/**
	 * @var string icon-name as used in <code>FA::icon($iconname)</code>.
	 * @see \rmrevin\yii\fontawesome\FA
	 */
	public $icon;

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
		$icon = empty($this->icon) ? '' : FA::icon($this->icon);
		if (empty($this->label) || strcmp($this->label, 'Button') === 0) {
			$label = '';
		} else {
			$label = Html::tag('span', $this->encodeLabel ? Html::encode($this->label) : $this->label);
		}

		return $icon . $label;
	}

}
