<?php
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

echo "<?php\n";
?>
namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") . ";\n" ?>

class <?= $searchModelClass ?> extends <?= '\\' . ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") . "\n" ?>
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			<?= implode(",\n            ", $rules) ?>,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		//bypass scenarios() implementation of the parent class
		return Model::scenarios();
	}

	/**
	 * Creates a data provider instance with the search query applied
	 *
	 * @param array $params the search params
	 * @return \yii\data\ActiveDataProvider the configured data provider
	 */
	public function search($params)
	{
		//create query instance
		$query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

		//create data provider instance
		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			/*
			'sort'=>[
				'defaultOrder'=>[
					'my_first_column'=>SORT_ASC,
					'my_second_column'=>SORT_ASC,
				],
			],
			*/
		]);

		//load the data
		$this->load($params);

		//apply filtering conditions
		<?= implode("\n\t\t", str_replace(' => ', '=>', $searchConditions)) ?>

		return $dataProvider;
	}

}
