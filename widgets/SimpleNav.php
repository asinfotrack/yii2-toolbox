<?php
namespace asinfotrack\yii2\toolbox\widgets;

use yii\helpers\Html;

/**
 * Simple navigation widget which has the same functionality as the
 * regular nav-widget (@see \yii\bootstrap\Nav) but renders a plain
 * and simple html-list which can then be further styled with css.
 * No dropdown and nothing!
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class SimpleNav extends \yii\bootstrap\Widget
{

	/**
	 * @var array list of items in the nav widget. Each array element represents a single
	 * menu item which can be either a string or an array with the following structure:
	 *
	 * - label: string, required, the nav item label.
	 * - url: optional, the item's URL. Defaults to "#".
	 * - visible: boolean, optional, whether this menu item is visible. Defaults to true.
	 * - linkOptions: array, optional, the HTML attributes of the item's link.
	 * - options: array, optional, the HTML attributes of the item container (LI).
	 * - active: boolean, optional, whether the item should be on active state or not.
	 * - dropDownOptions: array, optional, the HTML options that will passed to the [[Dropdown]] widget.
	 * - items: array|string, optional, the configuration array for creating a [[Dropdown]] widget,
	 *   or a string representing the dropdown menu. Note that Bootstrap does not support sub-dropdown menus.
	 *
	 * If a menu item is a string, it will be rendered directly without HTML encoding.
	 */
    public $items;
	/**
	 * @var integer max depth of items to render
	 */
	public $maxDepth;
	/**
	 * @var string|\Closure either a fixed string or a closure returning a string
	 * which will be prepended to each label text. The closure should have the signature
	 * 'function ($item)' where $item is the item config array.
	 */
	public $entryPrefix;
	/**
	 * @var bool whether or not to activate parent elements
	 */
	public $activateParents = true;
	/**
	 * @var string tag name to use when an item is not a link (no url present)
	 */
	public $noLinkTagName = 'span';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		Html::addCssClass($this->options, 'nav');

		//activate parents if desired
		if ($this->activateParents) {
			foreach ($this->items as &$item) {
				$this->activateItems($item);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		echo Html::beginTag('ul', $this->options);
		foreach ($this->items as $item) {
			echo $this->renderItem($item);
		}
		echo Html::endTag('ul');
	}

	/**
	 * Renders an actual item with its children recursively
	 *
	 * @param array $item the item config
	 * @param integer $depth the current depth
	 * @return string the resulting html code
	 */
	protected function renderItem($item, $depth=0)
	{
		//prepare options
		$options = isset($item['options']) ? $item['options'] : [];
		Html::addCssClass($options, 'depth-' . $depth);
		if (isset($item['active']) && $item['active']) Html::addCssClass($options, 'active');
		if (isset($item['items']) && count($item['items']) > 0) Html::addCssClass($options, 'is-parent');

		//prepare returned code
		$ret = Html::beginTag('li', $options);
		$ret .= $this->createEntry($item);

		//render children recursively
		if (isset($item['items']) && ($this->maxDepth === null || ($this->maxDepth !== null && $depth + 1 <= $this->maxDepth))) {
			$ret .= Html::beginTag('ul');
			foreach ($item['items'] as $subItem) {
				$ret .= $this->renderItem($subItem, $depth + 1);
			}
			$ret .= Html::endTag('ul');
		}

		//finish and return
		$ret .= Html::endTag('li');
		return $ret;
	}

	/**
	 * Creates the actual label-content of the item. Depending if an url
	 * is present it will be a link or otherwise the in the options defined tag
	 *
	 * @param array $item the item config
	 * @return string the resulting html code
	 */
	protected function createEntry($item)
	{
		$label = $item['label'];
		if ($this->entryPrefix !== null) {
			if ($this->entryPrefix instanceof \Closure) {
				$prefix = call_user_func($this->entryPrefix, $item);
			} else {
				$prefix = $this->entryPrefix;
			}
			$label = $prefix . $label;
		}

		if (isset($item['url'])) {
			return Html::a($label, $item['url']);
		} else {
			return Html::tag($this->noLinkTagName, $label);
		}
	}

	/**
	 * Traverses over an item and its children recursively to determine
	 * if it is active or not
	 *
	 * @param array $item the item config
	 * @return bool true if active
	 */
	protected function activateItems(&$item)
	{
		$hasActiveChild = false;
		if (isset($item['items'])) {
			foreach ($item['items'] as &$childItem) {
				if ($this->activateItems($childItem)) $hasActiveChild = true;
			}
		}

		$isActive = isset($item['active']) ? $item['active'] : $this->isItemActive($item);
		if (!$isActive && $hasActiveChild) $isActive = true;
		$item['active'] = $isActive;

		return $isActive;
	}

	/**
	 * Determines if an url is active or not.
	 *
	 * @param array $item the item config
	 * @return bool true if active
	 */
	protected function isItemActive($item)
	{
		if (!isset($item['url'])) return false;
		return \asinfotrack\yii2\toolbox\helpers\Url::isUrlActive($item['url']);
	}

}
