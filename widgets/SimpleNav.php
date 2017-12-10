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
 * @license AS infotrack AG license / MIT, see provided license file
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
	 * - options: array, optional, the HTML attributes of the item container (li).
	 * - itemOptions: array, optional, the HTML attributes for the actual link- or span-tag
	 * - active: boolean, optional, whether the item should be on active state or not.
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
	 * @var string|callable either a fixed string or a callable returning a string
	 * which will be prepended to each label text. The callable should have the signature
	 * 'function ($item, $depth)' where $item is the item config array and $depth the current
	 * depth of the item.
	 */
	public $entryPrefix;

	/**
	 * @var bool Whether or not the prefix should be rendered within the anchor-tag
	 */
	public $entryPrefixInsideLink = true;

	/**
	 * @var bool whether or not to activate items
	 */
	public $activateItems = true;

	/**
	 * @var callable if set, this callable will be called to determine if an item is active.
	 * The callable should have the signature 'function ($item)' where $item is the item config array.
	 */
	public $isActiveCallback;

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
		if ($this->activateItems) {
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
		//visibility
		if (isset($item['visible']) && $item['visible'] === false) return '';

		//prepare options
		$options = isset($item['options']) ? $item['options'] : [];
		Html::addCssClass($options, 'depth-' . $depth);
		if (isset($item['active']) && $item['active']) Html::addCssClass($options, 'active');
		if (isset($item['items']) && count($item['items']) > 0) Html::addCssClass($options, 'is-parent');

		//prepare returned code
		$ret = Html::beginTag('li', $options);
		$ret .= $this->createEntry($item, $depth);

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
	 * @param int $depth the depth of the item to create
	 * @return string the resulting html code
	 */
	protected function createEntry($item, $depth)
	{
		$label = $item['label'];
		$prefix = '';
		if ($this->entryPrefix !== null) {
			if (is_callable($this->entryPrefix)) {
				$prefix = call_user_func($this->entryPrefix, $item, $depth);
			} else {
				$prefix = $this->entryPrefix;
			}
		}

		$itemOptions = isset($item['itemOptions']) ? $item['itemOptions'] : [];
		if (isset($item['url'])) {
			if ($this->entryPrefixInsideLink) {
				return Html::a($prefix . $label, $item['url'], $itemOptions);
			} else {
				return $prefix . Html::a($label, $item['url'], $itemOptions);
			}
		} else {
			return Html::tag($this->noLinkTagName, $prefix . $label, $itemOptions);
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

		if (is_callable($this->isActiveCallback)) {
			return call_user_func($this->isActiveCallback, $item);
		} else {
			return \asinfotrack\yii2\toolbox\helpers\Url::isUrlActive($item['url']);
		}
	}

}
