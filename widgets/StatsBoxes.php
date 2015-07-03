<?php
namespace asinfotrack\yii2\toolbox\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use rmrevin\yii\fontawesome\FA;

/**
 * Renders stats-boxes containing a title, an optional icon and a value
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class StatsBoxes extends \yii\base\Widget
{

	/**
	 * @var array an array containing the field data. Each box is represented with an array and
	 * can contain the following keys:
	 * header:		the header of the box
	 * headerIcon:	font-awesome icon name. the icon gets prefixed to the header
	 * value:		holds the value of the box (either a string or a closure with no params)
	 * valuePrefix:	a prefix for the value (can be an icon, currency symbol, etc.)
	 * valueSuffix:	a suffix for the value (can be an icon, currency symbol, etc.)
	 * options:		specific options for an individual box
	 * visible:		either a boolean value or a closure returning a boolean type (format: 'function() { }')
	 */
	public $boxes = [];

	/**
	 * @var array the html options for the enclosing div container
	 */
	public $options = [];

	/**
	 * @var array the html options for the individual boxes
	 */
	public $boxOptions = [];

	/**
	 * @var string the tag used for the headers within the box. defaults to span.
	 */
	public $headerTagName = 'span';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//prepare container options
		Html::addCssClass($this->options, 'container-fluid');
		Html::addCssClass($this->options, 'widget-stats-boxes');
		$this->options = ArrayHelper::merge($this->options, ['id'=>$this->getId()]);

		//validate and prepare options for the individual boxes
		$boxIndexesInvisible = [];
		foreach ($this->boxes as $i=>&$b) {
			//validate required indexes
			if ($b['header'] === null || $b['value'] === null) {
				$msg = Yii::t('app', 'Each box needs a header and a value: index {index}', ['index'=>$i]);
				throw new InvalidConfigException($msg);
			}
			$hasVisibility = isset($b['visible']) && $b['visible'] !== null;
			if ($hasVisibility && !($b['visible'] instanceof \Closure) && !is_bool($b['visible'])) {
				$msg = Yii::t('app', 'Visible needs to be a boolean value or a closure');
				throw new InvalidConfigException($msg);
			}

			//check if box is invisible and remove it if so
			if ($hasVisibility && ((is_bool($b['visible']) && $b['visible'] === false) || ($b['visible'] instanceof \Closure && call_user_func($b['visible']) === false))) {
				$boxIndexesInvisible[] = $i;
				continue;
			}

			//prepare box options
			if (isset($b['options'])) {
				$b['options'] = ArrayHelper::merge($this->boxOptions, $b['options']);
			} else {
				$b['options'] = $this->boxOptions;
			}
		}

		//actually remove invisible boxes
		foreach ($boxIndexesInvisible as $i) unset($this->boxes[$i]);
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		//return if no boxes to display
		if (count($this->boxes) == 0) return;

		//run parent implementation
		parent::run();

		//render actual boxes
		echo Html::beginTag('div', $this->options);
		echo Html::beginTag('div', ['class'=>'row']);

		$colSize = floor(12 / count($this->boxes));
		foreach ($this->boxes as $b) {
			$this->renderBox($b, $colSize);
		}

		echo Html::endTag('div');
		echo Html::endTag('div');
	}

	/**
	 * Does the actual rendering of a single box
	 *
	 * @param array $boxData the box configuration data
	 * @param int $colSize the column size
	 */
	protected function renderBox($boxData, $colSize)
	{
		$options = $boxData['options'];
		Html::addCssClass($options, 'col-md-' . $colSize);
		Html::addCssClass($options, 'col-sm-' . ($colSize * 2));
		Html::addCssClass($options, 'stats-box');

		echo Html::beginTag('div', $options);
		echo Html::beginTag('div', ['class'=>'stats-box-content-wrapper']);

		//header
		echo Html::beginTag('div', ['class'=>'stats-box-header']);
		if (isset($boxData['headerIcon'])) echo FA::icon($boxData['headerIcon']);
		echo Html::tag($this->headerTagName, $boxData['header']);
		echo Html::endTag('div');

		//content
		echo Html::beginTag('div', ['class'=>'stats-box-content']);
		if (isset($boxData['valuePrefix'])) echo Html::tag('span', $boxData['valuePrefix'], ['class'=>'stats-box-content-prefix']);
		$value = $boxData['value'] instanceof \Closure ? call_user_func($boxData['value']) : $boxData['value'];
		echo Html::tag('span', $value, ['class'=>'stats-box-value']);
		if (isset($boxData['valueSuffix'])) echo Html::tag('span', $boxData['valueSuffix'], ['class'=>'stats-box-content-suffix']);
		echo Html::endTag('div');

		echo Html::endTag('div');
		echo Html::endTag('div');
	}

}
