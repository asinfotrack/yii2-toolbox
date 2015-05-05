<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use yii\helpers\Html;
use yii\base\InvalidConfigException;

/**
 * Extended DataColumn to provide width and alignment options.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class AdvancedDataColumn extends \yii\grid\DataColumn
{
	
	const TEXT_LEFT 	= 0;
	const TEXT_CENTER 	= 1;
	const TEXT_RIGHT	= 2;

	private static $LEGAL_ALIGNEMENTS = [self::TEXT_LEFT, self::TEXT_CENTER, self::TEXT_RIGHT];
	private static $CSS_CLASSES;
	
	/**
	 * @var string the css class for left-alignement (defaults to null, meaning no class)
	 */
	public $textAlignLeftClass 		= null;
	/**
	 * @var string the css class for center-alignement (defaults to 'text-center')
	 */
	public $textAlignCenterClass 	= 'text-center';
	/**
	 * @var string the css class for right-alignement (defaults to 'text-right')
	 */
	public $textAlignRightClass 	= 'text-right';
	
	/**
	 * @var string|integer the desired width of the column (can be values like '20%', '100px', '10em' or  
	 * numerical values which will also be used as percentages)
	 */
	public $columnWidth;
	/**
	 * @var string|integer the minimal width of the column (can be values like '20%', '100px', '10em' or
	 * numerical values which will also be used as percentages)
	 */
	public $minColumnWidth;
	/**
	 * @var string|integer the maximum width of the column (can be values like '20%', '100px', '10em' or
	 * numerical values which will also be used as percentages)
	 */
	public $maxColumnWidth;
	
	/**
	 * @var integer the alignement of ALL parts of this column (header, filter, content, footer). The values of this
	 * can be overridden by providing individual values for other parts. 
	 * Use the constants of this class to define the alignement (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignAll;
	/**
	 * @var integer the alignement of the header cell.
	 * Use the constants of this class to define the alignement (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignHeader;
	/**
	 * @var integer the alignement of the filter cell.
	 * Use the constants of this class to define the alignement (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignFilter;
	/**
	 * @var integer the alignement of the content cell.
	 * Use the constants of this class to define the alignement (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignContent;
	/**
	 * @var integer the alignement of the footer cell.
	 * Use the constants of this class to define the alignement (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignFooter; 
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Object::init()
	 */
	public function init()
	{
		//parent initialization
		parent::init();
		
		//cache css-classes-array
		if (self::$CSS_CLASSES == null) {
			$arrCssClasses = [$this->textAlignLeftClass, $this->textAlignCenterClass, $this->textAlignRightClass];
			self::$CSS_CLASSES = array_filter($arrCssClasses, function($key) use ($arrCssClasses) {
				return !empty($arrCssClasses[$key]);
			});
		}
		
		//apply settings
		$this->applyColumnWidths();
		$this->applyTextAlignements();
	}
	
	/**
	 * Applies the column width options. Existing width styles will be overriden.
	 */
	private function applyColumnWidths()
	{
		$styles = [];
		if (!empty($this->columnWidth)) $styles['width'] = $this->prepareWidthStyleValue($this->columnWidth);
		if (!empty($this->minColumnWidth)) $styles['min-width'] = $this->prepareWidthStyleValue($this->minColumnWidth);
		if (!empty($this->maxColumnWidth)) $styles['max-width'] = $this->prepareWidthStyleValue($this->maxColumnWidth);
		
		Html::addCssStyle($this->options, $styles, true);
	}
	
	/**
	 * Prepares a width value
	 * @param string|integer $val the value to prepare
	 * @return string the properly prepared value for a css-width-property
	 */
	private function prepareWidthStyleValue($val)
	{
		return is_numeric($val) ? $val . '%' : strtolower($val);
	}
	
	/**
	 * Applies the text alignement to the grid parts
	 */
	private function applyTextAlignements()
	{		
		//align all has precedence
		if ($this->textAlignAll != null) {
			$this->applyTextAlignementToOptions($this->headerOptions, $this->textAlignAll);
			$this->applyTextAlignementToOptions($this->filterOptions, $this->textAlignAll);
			$this->applyTextAlignementToOptions($this->filterInputOptions, $this->textAlignAll);
			$this->applyTextAlignementToOptions($this->contentOptions, $this->textAlignAll);
			$this->applyTextAlignementToOptions($this->footerOptions, $this->textAlignAll);
		}
		
		//individual
		$this->applyTextAlignementToOptions($this->headerOptions, $this->textAlignHeader);
		$this->applyTextAlignementToOptions($this->filterOptions, $this->textAlignFilter);
		$this->applyTextAlignementToOptions($this->filterInputOptions, $this->textAlignFilter);
		$this->applyTextAlignementToOptions($this->contentOptions, $this->textAlignContent);
		$this->applyTextAlignementToOptions($this->footerOptions, $this->textAlignFooter);
	}
	
	/**
	 * Applies the alignement to the options of a certain grid part
	 * @param array $options the grid part options
	 * @param integer $alignement the alignement constand (@see AdvancedDataColumn::TEXT_%alignement)
	 * @throws InvalidConfigException in case of an unknown alignement value
	 */
	private function applyTextAlignementToOptions(&$options, $alignement)
	{
		//catch illegal values
		if (!in_array($alignement, self::$LEGAL_ALIGNEMENTS)) {
			throw new InvalidConfigException(sprintf('Illegal text alignement value for %s!', self::className()));
		}
		
		//strip already applied classes classes
		foreach (self::$CSS_CLASSES as $cssClass) Html::removeCssClass($options, $cssClass);
		
		//apply actual css class
		switch ($alignement) {
			case null:
			case self::TEXT_LEFT:
				if ($this->textAlignLeftClass == null) break;
				Html::addCssClass($options, $this->textAlignLeftClass);
				break;
			case self::TEXT_CENTER:
				if ($this->textAlignCenterClass == null) break;
				Html::addCssClass($options, $this->textAlignCenterClass);
				break;
			case self::TEXT_RIGHT:
				if ($this->textAlignRightClass == null) break;
				Html::addCssClass($options, $this->textAlignRightClass);
				break;
		}
	}
	
}