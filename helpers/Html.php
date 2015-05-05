<?php
namespace asinfotrack\helpers;

use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This helper extends the basic functionality of the Yii2-Html-helper.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Html extends \yii\helpers\Html
{
	
	const BS_DEFAULT 	= 'default';
	const BS_PRIMARY 	= 'primary';
	const BS_SUCCESS	= 'success';
	const BS_INFO		= 'info';
	const BS_WARNING	= 'warning';
	const BS_DANGER		= 'danger';
	
	const BS_XS			= 'xs';
	const BS_SM			= 'sm';
	const BS_MD			= 'md';
	const BS_LG			= 'lg';
	
	/**
	 * Renders a quantity with its corresponding unit (eg '107.00 kg')
	 * 
	 * @param float $value the quantity
	 * @param string $unit the unit descriptor string
	 * @param integer $decimals number of decimals
	 * @param boolean $muted whether or not to mute the unit symbol
	 * @return string generated html-code
	 */
	public static function valueWithUnit($value, $unit, $decimals=2, $muted=true)
	{		
		return Yii::$app->formatter->asDecimal($value, $decimals)
			   . ' '
			   . $muted ? static::tag('span', $unit, ['class'=>'text-muted']) : $unit;
	}
	
	/**
	 * Creates a bootstrap-badge
	 * 
	 * @param string $content the content of the badge
	 * @param boolean $encode whether or not to encode the content (defaults to true)
	 * @return string generated html-code
	 */
	public static function bsBadge($content, $encode=true)
	{
		return static::tag('span', $encode ? static::encode($content) : $content, ['class'=>'badge']);
	}
	
	/**
	 * Creates a bootstrap label
	 * 
	 * @param string $content the content of the label
	 * @param string $type the bootstrap type (eg alert, danger, etc.) which can be
	 * set via constants of this class
	 * @param boolean $encode whether or not to encode the content (defaults to true)
	 * @return string generated html-code
	 */
	public static function bsLabel($content, $type=self::BS_DEFAULT, $encode=true)
	{
		return static::tag('span', $encode ? static::encode($content) : $content, [
			'class'=>'label label-' . $type,	
		]);
	}
	
	/**
	 * Creates a bootstrap list group.
	 * Each item can be specified as a simple string or an array. Subsequent are
	 * all valid indices of which \'content\' is mandatory.
	 *
	 * - content:	string				contains the content of the item (mandatory!)
	 * - options:	mixed[]				individual item options beeing merged with default item options
	 * - url:		string|array		either a raw url in string format or an array as needed by
	 * 									yiis url-helper (@see \yii\helpers\Url::to())
	 * - type:		string				the bootstrap type (eg alert, danger, etc.) which can be
	 * 									set via constants of this class
	 * - active		boolean|\Closure	either a boolean value or an anonymous function returning
	 * 									a boolean value
	 * - badgeVal	string				content of an optional badge rendered inside the item
	 * 
	 * @param string[]|mixed[] $items collection of items (each can be string or array)
	 * @param mixed[] $listOptions options for the list tag
	 * @param mixed[] $defaultItemOptions default options for list items (can be overriden in
	 * items individual options)
	 * @param string $listTagName tag name for the list (defaults to <code>ul</code>)
	 * @throws InvalidParamException if an item is specified in array-form and index \'content\'
	 * is not set
	 */
	public function bsListGroup($items, $listOptions=[], $defaultItemOptions=[], $listTagName='ul')
	{		
		//prepare vars
		static::addCssClass($defaultItemOptions, 'list-group-item');
		$ret = '';
		
		//iterate over items
		foreach ($items as $i) {
			if (is_array($i)) {
				//validate content
				if (!isset($i['content'])) {
					throw new InvalidParamException('the index \'content\' is mandatory for each list element');
				}
				//prepare options
				$itemOptions = $defaultItemOptions;
				if (isset($i['options'])) {
					$itemOptions = ArrayHelper::merge($itemOptions, $i['options']);
					static::addCssClass($itemOptions, 'list-group-item');
				}
				//prepare url if necessary
				$isLink = isset($i['url']);
				if ($isLink) {
					$itemOptions['href'] = is_array($i['url']) ? Url::to($i['url']) : $i['url'];
				}
				//bs type
				if (isset($i['type'])) {
					static::addCssClass($itemOptions, 'list-group-item-' . $i['type']);
				}
				//active
				if (isset($i['active'])) {
					$isActive = $i['active'] instanceof \Closure ? call_user_func($i['active']) : $i['active'];
					if ($isActive) static::addCssClass($itemOptions, 'active');
				}
				//badge
				if (isset($i['badgeVal'])) $i['content'] = static::bsBadge($i['badgeVal']) . $i['content'];
				//render item
				$ret .= static::tag($isLink ? 'a' : 'li', $i['content'], $itemOptions) . "\n";
			} else {
				$ret .= static::tag('li', $i, $defaultItemOptions);
			}
		}
		
		//enclose and return
		static::addCssClass($listOptions, 'list-group');
		return static::tag($listTagName, $ret, $listOptions);
	}
	
}