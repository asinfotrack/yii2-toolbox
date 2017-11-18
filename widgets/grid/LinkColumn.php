<?php
namespace asinfotrack\yii2\toolbox\widgets\grid;

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Column which makes links out of its contents. The link can be provided
 * in several formats.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class LinkColumn extends \asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn
{

	/**
	 * @var string|array|\Closure can hold a static url string, a closure in the format
	 * `function ($model, $key, $index, $widget)` or an array containing a route as used.
	 * width <code>Url::to($route)</code>
	 */
	public $link;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//check format
		if ($this->hasLink() && !in_array($this->format, ['html', 'raw'])) {
			$this->format = 'html';
		}

		//parent initialization
		parent::init();
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		$content = parent::renderDataCellContent($model, $key, $index);
		$parsedLink = $this->parseUrl($model, $key, $index);
		if ($this->hasLink() && $parsedLink !== null) {
			return Html::a($content, $parsedLink);
		} else {
			return $content;
		}
	}

	/**
	 * Parses the provided link-value into the final url for the link
	 *
	 * @param mixed $model the data model
	 * @param mixed $key the key associated with the data model
	 * @param integer $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
	 * @return string|null final url string or null
	 */
	protected function parseUrl($model, $key, $index)
	{
		//catch no link
		if (!$this->hasLink()) return null;

		//prepare link
		if (is_array($this->link)) {
			return Url::to($this->link);
		} else if ($this->link instanceof \Closure) {
			return call_user_func($this->link, $model, $key, $index, $this);
		} else {
			return $this->link;
		}
	}

	/**
	 * Checks if there is a link present
	 *
	 * @return boolean true if there is a link
	 */
	protected function hasLink()
	{
		return $this->link !== null;
	}

}
