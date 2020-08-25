<?php
namespace asinfotrack\yii2\toolbox\widgets;

use Yii;
use yii\bootstrap\BootstrapAsset;
use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\JsExpression;

/**
 * Tabs widget which remembers its active tab via javascript storage
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class TabsWithMemory extends \yii\bootstrap\Tabs
{

	const STORAGE_SESSION = 'sessionStorage';
	const STORAGE_LOCAL = 'localStorage';

	/**
	 * @var bool static marker to show if js was registered before
	 */
	protected static $JS_REGISTERED = false;

	/**
	 * @var string defines which type of storage should be used. the two possible
	 * options are available as constants in the widget.
	 */
	public $storageType = self::STORAGE_SESSION;

	/**
	 * @var string optional: Defines the key to be used to store the tab index
	 */
	public $storageKey;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		if (null === $this->storageKey) {
			$this->storageKey = $this->generateStorageKey();
		}
		if (!isset($this->options['data'])) {
			$this->options['data'] = [];
		}
		$this->options['data']['storage-key'] = $this->storageKey;

		Html::addCssClass($this->options, 'widget-memory-tabs');
		$this->registerJs();
	}

	/**
	 * Gets a default storage key which is the current absolute url.
	 *
	 * @return string The current absolute url
	 */
	protected function generateStorageKey()
	{
		return Yii::$app->request->absoluteUrl;
	}

	/**
	 * Registers the js code if necessary
	 */
	protected function registerJs()
	{
		if (static::$JS_REGISTERED) return;

		JqueryAsset::register($this->getView());
		BootstrapAsset::register($this->getView());


		$js = new JsExpression(<<<JS
			var widgetClass = 'widget-memory-tabs';
			var storageName = 'widget-memory-tabs';

			var hasStorage = function() {
				var test = 'test';
				try {
					{$this->storageType}.setItem(test, test);
					{$this->storageType}.removeItem(test);
					return true;
				} catch(e) {
					return false;
				}
			};

			if (hasStorage) {

				var loadData = function() {
					var dataStr = {$this->storageType}.getItem(storageName);
					if (dataStr === null || dataStr.length === 0) return {};
					return JSON.parse(dataStr);
				};

				var saveData = function(dataObj) {
					dataStr = JSON.stringify(dataObj);
					{$this->storageType}.setItem(storageName, dataStr);
				};

				var activateIndex = function(tabId, index) {
					var tab = $('#' + tabId);
					var items = tab.children('li');
					if (items.length <= index) return;

					$('#' + tabId + ' li:eq(' + index + ') a').tab('show');
				};
				
				var getBaseUrlByAnchor = function(url) {
					var hashTagIndex = url.indexOf('#',0);
					if (hashTagIndex === -1) return null;
					return url.substring(0, hashTagIndex);
				};

				var initIndexes = function() {
					var data = loadData();
					var widgets = document.querySelectorAll('.' + widgetClass);
					widgets.forEach(function(widget) {
						var jqWidget = $(widget);
						var storageKey = jqWidget.data('storage-key');

						var tabId = jqWidget.attr('id');
						if (tabId !== null) {
							var index = data[storageKey][tabId];
							activateIndex(tabId, index);
						}
					});
				};

				var setIndex = function(tabId, index) {
					var data = loadData();
					var jqWidget = $('#' + tabId);
					var storageKey = jqWidget.data('storage-key');
					data[storageKey][tabId] = index;
					saveData(data);
				};

				$('.widget-memory-tabs > li > a').mouseup(function(event) {
					var tabs = $(this).closest('.' + widgetClass);
					var selectedIndex = $(this).parent().prevAll().length;

					setIndex(tabs.attr('id'), selectedIndex);
				});

				initIndexes();
			}
JS
);
		$this->view->registerJs($js);

		static::$JS_REGISTERED = true;
	}
}
