<?php
namespace asinfotrack\yii2\toolbox\helpers;

use yii\base\Object;
use yii\helpers\Json;

/**
 * Helper for dropdown lists
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class DropdownHelper
{

	/**
	 * This method loads and maps models optionally with additional data-attributes for dropdown-lists. You can use it
	 * like this:
	 *
	 * <code>
	 *     <?php
	 *         $rateData = DropdownHelper::fetchItemsWithData(Rate::find()->orderTitle(), 'id', 'title', ['currency','per_hour','per_km']);
	 *     ?>
	 *     <?= $form->field($model, 'rate_id')->dropDownList($rateData['items'], ['prompt'=>Yii::t('app', 'Choose one...'), 'options'=>$rateData['itemOptions']]) ?>
	 * </code>
	 *
	 * @param \yii\db\ActiveQuery $query the prepared query to fetch the data for
	 * @param string $from the attribute to map from (usually the id of the model)
	 * @param string $to the attribute to map to (usually something like `name`). Note that this can also be a closure
	 * with the signature `function ($model)` returning a string
	 * @param string[] $dataAttributes list of model attributes which will be loaded as additional `data-XXX` fields
	 * for the dropdown-options
	 * @return array an array with the keys `items` and `itemOptions`. The first contains the items for the dropdown
	 * and second their additional data-options if specified
	 */
	public static function fetchItemsWithData($query, $from, $to, $dataAttributes=[])
	{
		/* @var $models \yii\db\ActiveRecord[] */

		//prepare returned vars
		$items = [];
		$itemOptions = [];

		//load data
		$models = $query->all();

		//iterate
		foreach ($models as $model) {
			//item
			$valFrom = $model->{$from};
			$valTo = $to instanceof \Closure ? call_user_func($to, $model) : $model->{$to};
			$items[$valFrom] = $valTo;

			//check if there are item options or continue
			if (empty($dataAttributes)) continue;

			//fetch item options
			$itemOptions[$valFrom] = [];
			foreach ($dataAttributes as $dataAttr) {
				if (!isset($itemOptions[$valFrom]['data'])) $itemOptions[$valFrom]['data'] = [];

				//prepare
				$parts = explode('.', $dataAttr);
				$identifier = implode('-', $parts);

				//fetch value
				$valOrObj = $model;
				foreach ($parts as $part) {
					$valOrObj = $valOrObj->{$part};
				}

				//set options
				$itemOptions[$valFrom]['data'][$identifier] = $valOrObj instanceof Object || is_array($valOrObj) ? Json::encode($valOrObj) : $valOrObj;
			}
		}

		//return
		return ['items'=>$items, 'itemOptions'=>$itemOptions];
	}

}
