<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use yii\helpers\Html;
use yii\base\InvalidConfigException;

/**
 * Extended DataColumn to provide width and alignment options.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AdvancedDataColumn extends \yii\grid\DataColumn
{

	const TEXT_LEFT 	= 0;
	const TEXT_CENTER 	= 1;
	const TEXT_RIGHT	= 2;

	private static $LEGAL_ALIGNMENTS = [self::TEXT_LEFT, self::TEXT_CENTER, self::TEXT_RIGHT];
	private static $CSS_CLASSES;

	/**
	 * @var string the css class for left-alignment (defaults to null, meaning no class)
	 */
	public $textAlignLeftClass 		= null;
	/**
	 * @var string the css class for center-alignment (defaults to 'text-center')
	 */
	public $textAlignCenterClass 	= 'text-center';
	/**
	 * @var string the css class for right-alignment (defaults to 'text-right')
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
	 * @var integer the alignment of ALL parts of this column (header, filter, content, footer). The values of this
	 * can be overridden by providing individual values for other parts.
	 * Use the constants of this class to define the alignment (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignAll;
	/**
	 * @var integer the alignment of the header cell.
	 * Use the constants of this class to define the alignment (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignHeader;
	/**
	 * @var integer the alignment of the filter cell.
	 * Use the constants of this class to define the alignment (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignFilter;
	/**
	 * @var integer the alignment of the content cell.
	 * Use the constants of this class to define the alignment (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignContent;
	/**
	 * @var integer the alignment of the footer cell.
	 * Use the constants of this class to define the alignment (@see AdvancedDataColumn::TEXT_%align)
	 */
	public $textAlignFooter;

	/**
	 * @inheritdoc
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
		$this->applyTextAlignments();
	}

	/**
	 * Applies the column width options. Existing width styles will be overridden.
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
	 * Applies the text alignment to the grid parts
	 */
	private function applyTextAlignments()
	{
		//align all has precedence
		if ($this->textAlignAll != null) {
			$this->applyTextAlignmentToOptions($this->headerOptions, $this->textAlignAll);
			$this->applyTextAlignmentToOptions($this->filterOptions, $this->textAlignAll);
			$this->applyTextAlignmentToOptions($this->filterInputOptions, $this->textAlignAll);
			$this->applyTextAlignmentToOptions($this->contentOptions, $this->textAlignAll);
			$this->applyTextAlignmentToOptions($this->footerOptions, $this->textAlignAll);
		}

		//individual
		$this->applyTextAlignmentToOptions($this->headerOptions, $this->textAlignHeader);
		$this->applyTextAlignmentToOptions($this->filterOptions, $this->textAlignFilter);
		$this->applyTextAlignmentToOptions($this->filterInputOptions, $this->textAlignFilter);
		$this->applyTextAlignmentToOptions($this->contentOptions, $this->textAlignContent);
		$this->applyTextAlignmentToOptions($this->footerOptions, $this->textAlignFooter);
	}

	/**
	 * Applies the alignment to the options of a certain grid part
	 * @param array $options the grid part options
	 * @param integer $alignment the alignment constant (@see AdvancedDataColumn::TEXT_%alignment)
	 * @throws InvalidConfigException in case of an unknown alignment value
	 */
	private function applyTextAlignmentToOptions(&$options, $alignment)
	{
		//catch illegal values
		if (!in_array($alignment, self::$LEGAL_ALIGNMENTS)) {
			throw new InvalidConfigException(sprintf('Illegal text alignment value for %s!', self::className()));
		}

		//strip already applied classes classes
		foreach (self::$CSS_CLASSES as $cssClass) Html::removeCssClass($options, $cssClass);

		//apply actual css class
		switch ($alignment) {
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
