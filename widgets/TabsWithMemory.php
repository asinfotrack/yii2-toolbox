<?php
namespace asinfotrack\yii2\toolbox\widgets;

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

	/**
	 * @var bool static marker to show if js was registered before
	 */
	protected static $JS_REGISTERED = false;

	/**
	 * @var string defines which type of storage should be used. eg sessionStorage, localStorage etc.
	 */
	public $storageType = 'sessionStorage';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		Html::addCssClass($this->options, 'widget-memory-tabs');
		$this->registerJs();
	}

	/**
	 * Registers the js code if necessary
	 */
	protected function registerJs()
	{
		if (static::$JS_REGISTERED) return;

		JqueryAsset::register($this->getView());
		BootstrapAsset::register($this->getView());

		$js = new JsExpression("
			var widgetClass = 'widget-memory-tabs';
			var storageName = 'widget-memory-tabs';

			var hasStorage = function() {
				var test = 'test';
				try {
					" . $this->storageType . ".setItem(test, test);
					" . $this->storageType . ".removeItem(test);
					return true;
				} catch(e) {
					return false;
				}
			};

			if (hasStorage) {

				var loadData = function() {
					var dataStr = " . $this->storageType . ".getItem(storageName);
					if (dataStr == null) return {};
					return JSON.parse(dataStr);
				};

				var saveData = function(dataObj) {
					dataStr = JSON.stringify(dataObj);
					" . $this->storageType . ".setItem(storageName, dataStr);
				};

				var activateIndex = function(tabId, index) {
					var tab = $('#' + tabId);
					var items = tab.children('li');
					if (items.length <= index) return;

					$('#' + tabId + ' li:eq(' + index + ') a').tab('show');
				};

				var initIndexes = function() {
					var data = loadData();
					var curUrl = window.location.href;
					if (data[curUrl] == null) return;

					var tabs = $('.' + widgetClass);
					tabs.each(function(i, el) {
						var tabId = $(this).attr('id');
						if (tabId != null) {
							var index = data[curUrl][tabId];
							activateIndex(tabId, index);
						}
					});
				};

				var setIndex = function(tabId, index) {
					var curUrl = window.location.href;
					var data = loadData();
					if (data[curUrl] == null) data[curUrl] = {};
					data[curUrl][tabId] = index;

					saveData(data);
				};

				$('.widget-memory-tabs > li > a').mouseup(function(event) {
					var tabs = $(this).closest('.' + widgetClass);
					var selectedIndex = $(this).parent().prevAll().length;

					setIndex(tabs.attr('id'), selectedIndex);
				});

				initIndexes();
			}
		");
		$this->view->registerJs($js);

		static::$JS_REGISTERED = true;
	}

}
