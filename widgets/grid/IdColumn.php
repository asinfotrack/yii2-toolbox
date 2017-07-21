<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use Yii;
use yii\helpers\Html;

/**
 * Column optimized for rendering id values
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class IdColumn extends \asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn
{

	/**
	 * @var boolean|\Closure boolean value or closure in format 'function()' returning
	 * a boolean value. If set to true, the content will be wrapped within a code tag.
	 */
	public $useCodeTag = false;

	/**
	 * @var array html options for the code tag (only relevant if code tag is used)
	 */
	public $codeTagOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//apply default config for id column
		if (empty($this->columnWidth)) $this->columnWidth = '100px';
		if (empty($this->textAlignAll)) $this->textAlignAll = self::TEXT_CENTER;

		//code tag config
		if ($this->useCodeTag && !in_array($this->format, ['html', 'raw'])) {
			$this->format = 'html';
		}

		//call parent implementation
		parent::init();
	}

	/**
	 * @inheritdoc
	 */
	public function renderDataCellContent($model, $key, $index)
	{
		$content = parent::renderDataCellContent($model, $key, $index);

		if ($this->useCodeTag && $content != null && $content != Yii::$app->formatter->nullDisplay) {
			return Html::tag('code', $content, $this->codeTagOptions);
		} else {
			return $content;
		}
	}

}
