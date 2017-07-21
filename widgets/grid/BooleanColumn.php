<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use Yii;

/**
 * Shorthand column type to configure columns for boolean values
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class BooleanColumn extends \asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn
{
	
	/**
	 * @var array holds the boolean filter value cached
	 */
	private static $BOOL_FILTER;
	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//prepare bool filter if necessary
		if (self::$BOOL_FILTER === null) {
			self::$BOOL_FILTER = [
				1=>Yii::t('yii', 'Yes'),
				0=>Yii::T('yii', 'No'),	
			];
		}		
		
		//data column settings
		$this->format = 'boolean';
		if (!isset($this->filter)) $this->filter = self::$BOOL_FILTER;
		
		//css column settings
		if (!isset($this->columnWidth)) $this->columnWidth = 5;
		if (!isset($this->textAlignAll)) $this->textAlignAll = self::TEXT_CENTER;
		
		//parent initialization
		parent::init();
	}
	
}
