<?php
/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this \<?= ltrim($generator->viewBaseClass, '\\') ?> */
/* @var $model \<?= ltrim($generator->modelClass, '\\') ?> */

$this->title = Yii::t('<?= $generator->messageCategory ?>', 'Detail of {name}', [
	'name'=>$model-><?= $generator->getNameAttribute() ?>,
]);
?>

<?= "<?= " ?>DetailView::widget([
	'model'=>$model,
	'attributes'=>[
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
	foreach ($generator->getColumnNames() as $name) {
		echo "		'" . $name . "',\n";
	}
} else {
	foreach ($generator->getTableSchema()->columns as $column) {
		$format = $generator->generateColumnFormat($column);
		echo "		'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
	}
}
?>
	],
]) ?>
