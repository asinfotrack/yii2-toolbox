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
 * @license MIT
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
			$msg = Yii::t('app', 'Please sepcify an action to call');
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
		$this->options['data']['current-value'] = $this->model->{$this->booleanAttribute};
		$this->options['data']['pjax'] = 0;
		$this->options['data']['boolean-format'] = $this->booleanFormat;
		$this->options['data']['ajax-params'] = $params;
		$this->options['data']['ajax-method'] = $this->ajaxMethod;

		return Html::a($this->createLabel(), $this->ajaxUrl, $this->options);
	}

}
