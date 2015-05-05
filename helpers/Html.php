<?php
namespace asinfotrack\helpers;

/**
 * This helper extends the basic functionality of the Yii2-Html-helper.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Html extends \yii\helpers\Html
{
	
	/**
	 * Renders a financial amount with its currency
	 * @param decimal $value the amount
	 * @param string $currency the currency symbol (eg CHF or USD)
	 * @param integer $decimals number of decimals
	 * @param boolean $muted whether or not to mute the currency symbol
	 * @return string generated html-code
	 */
	public static function currencyAmount($value, $currency, $decimals=2, $muted=true)
	{
		return Yii::$app->formatter->asDecimal($value, $decimals)
			   . ' ' 
			   . static::tag('span', strtoupper($currency), ['class'=>$muted ? 'text-muted' : '']);
	}
	
	/**
	 * Renders a quantity with its corresponding unit
	 * @param decimal $value the quantity
	 * @param string $unit the unit descriptor string
	 * @param number $decimals number of decimals
	 * @param boolean $muted whether or not to mute the unit symbol
	 * @return string generated html-code
	 */
	public static function valueWithUnit($value, $unit, $decimals=2, $muted=true)
	{
		return Yii::$app->formatter->asDecimal($value, $decimals)
			   . ' '
			   . static::tag('span', $unit, ['class'=>$muted ? 'text-muted' : '']);
	}
	
	/**
	 * Creates a bootstrap-badge
	 * @param string $content the content of the badge
	 * @param boolean $encodeContent whether or not to encode the content (defaults to true)
	 * @return string generated html-code
	 */
	public static function badge($content, $encodeContent=true)
	{
		return static::tag('span', $encodeContent ? static::encode($content) : $content, ['class'=>'badge']);
	}
	
}