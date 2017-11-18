<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\Json;

/**
 * Helper class to work with primary keys of ActiveRecord-models
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class PrimaryKey
{

	/**
	 * Returns a models primary-key in json format. This works also with
	 * composite primary-keys
	 *
	 * @param \yii\db\ActiveRecord $model the model instance
	 * @return string the models pk in json-format
	 * @throws \yii\base\InvalidParamException if the model is not of type ActiveRecord
	 * @throws \yii\base\InvalidConfigException if the models pk is empty or invalid
	 */
	public static function asJson($model) {
		//check if the model is valid
		if (!($model instanceof \yii\db\ActiveRecord)) {
			throw new InvalidParamException(Yii::t('app', 'The model must be of type ActiveRecord'));
		}

		//fetch the models pk
		$pk = $model->primaryKey();

		//assert that a valid pk was received
		if ($pk === null || !is_array($pk) || count($pk) == 0) {
			$msg = Yii::t('app', 'Invalid primary key definition: please provide a pk-definition for table {table}', ['table'=>$model->tableName()]);
			throw new InvalidConfigException($msg);
		}

		//create final array and return it
		$arrPk = [];
		foreach ($pk as $pkCol) $arrPk[$pkCol] = $model->{$pkCol};
		return Json::encode($arrPk);
	}

}
