<?php
namespace asinfotrack\yii2\toolbox\actions;

use Yii;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use asinfotrack\yii2\toolbox\helpers\ComponentConfig;

/**
 * An action which changes a certain attribute via an ajax-call
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AjaxAttributeAction extends \yii\base\Action
{

	/**
	 * @var \yii\db\ActiveRecord the instance of the target class for internal use
	 */
	protected $modelInstance;

	/**
	 * @var string class of the model which must be of type active record
	 */
	public $targetClass;

	/**
	 * @var string the target attribute
	 */
	public $targetAttribute;

	/**
	 * @var string the name of the param which holds the new value during the ajax-request
	 */
	public $valueParamName = 'value';

	/**
	 * @var \Closure an optional callback to check whether the operation is allowed or not.
	 * It must be in the following format: `function ($model, $attribute, $desiredValue)`.
	 * The method must result true if ok. Otherwise it can return false or a message-string
	 * which will then be used for the exception thrown.
	 *
	 * The attribute is validated anyway while saved...this is to perform additional checks
	 * like depending models values.
	 */
	public $isAllowedCallback;

	/**
	 * @inheritdoc
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		//validate model is active record
		$this->modelInstance = new $this->targetClass;
		ComponentConfig::isActiveRecord($this->modelInstance, true);
	}

	public function run()
	{
		//only ajax and post calls allowed
		if (!Yii::$app->request->isAjax) throw new InvalidCallException(Yii::t('app', 'Only Ajax-calls allowed'));
		if (!Yii::$app->request->isPost) throw new InvalidCallException(Yii::t('app', 'Only method post allowed'));

		//fetch model
		$data = Yii::$app->request->post();
		$model = $this->findModel($data);

		//get value
		$value = isset($data[$this->valueParamName]) ? $data[$this->valueParamName] : null;

		//check if change is allowed and perform the change if so
		if ($this->isAllowedCallback !== null) {
			$res = call_user_func($this->isAllowedCallback, $model, $this->targetAttribute, $value);
			if ($res !== true) {
				$msg = is_string($res) ? $res : Yii::t('app', 'The attribute can\'t be changed now');
				throw new Exception($msg);
			}
		}
		$model->{$this->targetAttribute} = $value;

		//act according to whether the attribute changed or not
		Yii::$app->response->format = Response::FORMAT_RAW;
		if (isset($model->dirtyAttributes[$this->targetAttribute])) {
			$attrLabel = $model->getAttributeLabel($this->targetAttribute);

			//validate attribute
			if (!$model->validate($this->targetAttribute)) {
				Yii::$app->response->statusCode = 400;
				$errors = $model->getErrors($this->targetAttribute);

				return Yii::t('app', 'Error while setting {attr}: {err}', [
					'attr'=>$attrLabel,
					'err'=>empty($errors) ? '?' : implode(', ', $errors),
				]);
			}

			//save attribute
			if ($model->save(false, [$this->targetAttribute])) {
				Yii::$app->response->statusCode = 200;
				return $value;
			} else {
				Yii::$app->response->statusCode = 501;
				return Yii::t('app', 'Error while saving attribute {attr}', ['attr'=>$attrLabel]);
			}
		} else {
			Yii::$app->response->statusCode = 200;
			return Yii::t('app', 'Model was not changed');
		}
	}

	/**
	 * Finds a model with the provided post data
	 *
	 * @param array $data the data to read the pk values from
	 * @return \yii\db\ActiveRecord the found model
	 * @throws \yii\base\InvalidConfigException if pk parts are missing in post-data
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($data)
	{
		//fetch pk data
		$pk = [];
		foreach ($this->modelInstance->primaryKey() as $pkCol) {
			if (!isset($data[$pkCol])) {
				throw new InvalidConfigException(Yii::t('app', 'Missing PK-param {param}', ['param' => $pkCol]));
			}
			$pk[$pkCol] = $data[$pkCol];
		}

		//find model and act according to result
		$model = $this->modelInstance->findOne($pk);
		if ($model === null) throw new NotFoundHttpException();
		return $model;
	}

}
