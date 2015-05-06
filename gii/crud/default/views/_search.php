<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>
use asinfotrack\yii2\toolbox\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this <?= $generator->getViewBaseClass(); ?> */
/* @var $form yii\widgets\ActiveForm */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">

	<?= "<?php " ?>$form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
	]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
	if (++$count < 6) {
		echo "\t\t<?= " . $generator->generateActiveSearchField($attribute) . " ?>\n";
	} else {
		echo "\t\t<?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n";
	}
}
?>

		<div class="form-group">
			<?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Speichern') ?>, ['class' => 'btn btn-primary']) ?>
			<?= "<?= " ?>Html::resetButton(<?= $generator->generateString('Zurücksetzen') ?>, ['class' => 'btn btn-default']) ?>
		</div>

	<?= "<?php " ?>ActiveForm::end(); ?>

</div>
