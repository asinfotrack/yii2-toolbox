<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this <?= $generator->getViewBaseClass(); ?> */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = 'Detail of ' . $model-><?= $generator->getNameAttribute() ?>;
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
