<?php
namespace asinfotrack\yii2\toolbox\helpers;

/**
 * Helper class to work with queries and abstract reoccuring tasks
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class QueryHelper
{

	/**
	 * Configures the query as such, that you can filter by a model, its id or an array of both. It is also
	 * possible to invert the query. This means all but the one(s) provided.
	 *
	 * @param \yii\db\ActiveQuery $query the query to modify
	 * @param integer|integer[]|\yii\db\ActiveRecord|\yii\db\ActiveRecord[] $param the id(s) or the model(s). If
	 * an array is provided, it can be a mix of both
	 * @param string $attribute the attribute name to compare (defaults to `id`)
	 * @param bool $invert if true t, the query will be inverted (NOT LIKE, NOT, ...)
	 */
	public function oneOrManyModelComparison(&$query, $param, $attribute='id', $invert=false)
	{
		//get data from array
		if (is_array($param)) {
			$data = [];
			foreach ($param as $p) {
				if ($p instanceof \yii\db\ActiveRecord) {
					$data[] = $p->{$attribute};
				} else {
					$data[] = $p;
				}
			}
			$param = $data;
		} else if ($param instanceof \yii\db\ActiveRecord) {
			$param = $param->{$attribute};
		}

		//modify query
		if (!$invert) {
			$query->andWhere([$attribute=>$param]);
		} else {
			$query->andWhere(['not', [$attribute=>$param]]);
		}
	}

}
