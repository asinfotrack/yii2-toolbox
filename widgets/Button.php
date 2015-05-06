<?php
namespace asinfotrack\yii2\toolbox\widgets;

use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;

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
	 * (non-PHPdoc)
	 * @see \yii\base\Widget::run()
	 */
	public function run()
	{
		$this->registerPlugin('button');
		
		$icon = empty($this->icon) ? '' : FA::icon($this->icon);
		$label = Html::tag('span', $this->encodeLabel ? Html::encode($this->label) : $this->label);
		
		return Html::tag($this->tagName, $icon . $label, $this->options);
	}
	
}
