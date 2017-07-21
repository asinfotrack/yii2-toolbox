<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>
use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?php if ($generator->indexWidgetType === 'grid'): ?>
use yii\grid\SerialColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
<?php endif; ?>

/* @var $this \<?= ltrim($generator->viewBaseClass, '\\') ?> */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \<?= ltrim($generator->searchModelClass, '\\') ?> */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
?>

<?= "<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('partials/_search', ['model' => $searchModel]); ?>

<?php if ($generator->indexWidgetType === 'grid'): ?>
<?= "<?= " ?>GridView::widget([
	'dataProvider'=>$dataProvider,
	'filterModel'=>$searchModel,
	'columns'=>[
		['class'=>SerialColumn::className()],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "		[\n";
		echo "			'attribute'=>'" . $name . "',\n";
		echo "		],\n";
	}
} else {
	foreach ($tableSchema->columns as $column) {
		$format = $generator->generateColumnFormat($column);
		echo "		[\n";
		echo "			'attribute'=>'" . $column->name . "',\n";
		if ($format !== 'text') {
			echo "			'format'=>'" . $format . "',\n";
		}
		echo "		],\n";
	}
}
?>

		['class'=>AdvancedActionColumn::className()],
	],
]); ?>
<?php else: ?>
<?= "<?= " ?>ListView::widget([
	'dataProvider'=>$dataProvider,
	'itemOptions'=>['class'=>'item'],
	'itemView'=>function($model, $key, $index, $widget) {
		return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
	},
]) ?>
<?php endif; ?>
