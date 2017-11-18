<?php
namespace asinfotrack\yii2\toolbox\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use asinfotrack\yii2\toolbox\assets\AjaxToggleButtonAsset;
use asinfotrack\yii2\toolbox\helpers\ComponentConfig;

/**
 * Button which toggles boolean values via ajax. Ideally used together with
 * `AjaxAttributeAction`.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AjaxToggleButton extends Button
{

	/**
	 * @var string holds the ajax url
	 */
	protected $ajaxUrl;

	/**
	 * @var \yii\db\ActiveRecord the model to toggle the attribute upon
	 */
	public $model;

	/**
	 * @var string the attribute to toggle
	 */
	public $booleanAttribute;

	/**
	 * @var string the controller to call. If not set, it is determined via the model class
	 */
	public $controller;

	/**
	 * @var string the action to call
	 */
	public $action;

	/**
	 * @var string the method for the ajax call to use
	 */
	public $ajaxMethod = 'post';

	/**
	 * @var array optional overridden boolean format. This needs to be an array with indexes
	 * 0 and 1 with corresponding string values if set
	 */
	public $booleanFormat;

	/**
	 * @var string holds the css class being active when the attribute is true
	 */
	public $buttonClassOn = 'btn-success';

	/**
	 * @var string holds the css class being active when the attribute is false
	 */
	public $buttonClassOff = 'btn-primary';

	/**
	 * @var string name of the js event triggered on window object upon successful toggling.
	 * The js-listener should have four params: `function(event, btn, pk, newVal)`:
	 *
	 * - event: 		the jquery event-object
	 * - btn: 			reference to the button node in the dom
	 * - pk: 			object holding the pk of the changed model
	 * - newVal: 		boolean indicating the new value of the attribute changed
	 */
	public $jsEventSuccess = 'ajax-toggle-button:success';

	/**
	 * @var string name of js event triggered on window object upon error while toggling
	 * The js listener should have five params: `function(event, btn, pk, textStatus, errorThrown)`:
	 *
	 * - event: 		the jquery event-object
	 * - btn: 			reference to the button node in the dom
	 * - pk: 			object holding the pk of the changed model
	 * - textStatus: 	the status text as received by jquery ajax
	 * - errorThrown: 	the error thrown as received by jquery ajax
	 */
	public $jsEventError = 'ajax-toggle-button:error';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//assert proper model is set
		if ($this->model === null || !ComponentConfig::isActiveRecord($this->model)) {
			$msg = Yii::t('app', 'Please set a proper model of type active record');
			throw new InvalidConfigException($msg);
		}

		//assert action is set
		if (empty($this->action)) {
			$msg = Yii::t('app', 'Please specify an action to call');
			throw new InvalidConfigException($msg);
		}

		//fetch the boolean format
		if ($this->booleanFormat === null) {
			$this->booleanFormat = Yii::$app->formatter->booleanFormat;
		}

		//prepare ajax url
		$controller = $this->controller !== null ? $this->controller : Inflector::camel2id(StringHelper::basename($this->model->className()));
		$this->ajaxUrl = Url::to(['/' . $controller . '/' . $this->action]);

		//set label
		$this->label = Yii::$app->formatter->asBoolean($this->model->{$this->booleanAttribute});
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		//register assets
		$this->registerPlugin('button');
		AjaxToggleButtonAsset::register($this->getView());

		//prepare ajax params
		$params = [];
		foreach ($this->model->primaryKey() as $pkCol) {
			$params[$pkCol] = $this->model->{$pkCol};
		}

		Html::addCssClass($this->options, 'widget-ajax-button');
		if ($this->model->{$this->booleanAttribute}) {
			Html::removeCssClass($this->options, $this->buttonClassOff);
			Html::addCssClass($this->options, $this->buttonClassOn);
		} else {
			Html::removeCssClass($this->options, $this->buttonClassOn);
			Html::addCssClass($this->options, $this->buttonClassOff);
		}

		$this->options['data']['current-value'] = $this->model->{$this->booleanAttribute};
		$this->options['data']['pjax'] = 0;
		$this->options['data']['boolean-format'] = $this->booleanFormat;
		$this->options['data']['ajax-params'] = $params;
		$this->options['data']['ajax-method'] = $this->ajaxMethod;
		$this->options['data']['event-success'] = $this->jsEventSuccess;
		$this->options['data']['event-error'] = $this->jsEventError;
		$this->options['data']['class-on'] = $this->buttonClassOn;
		$this->options['data']['class-off'] = $this->buttonClassOff;

		return Html::a($this->createLabel(), $this->ajaxUrl, $this->options);
	}

}
