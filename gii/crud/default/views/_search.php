<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

echo "<?php\n";
?>
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \<?= ltrim($generator->viewBaseClass, '\\') ?> */
/* @var $form \yii\widgets\ActiveForm */
/* @var $model \<?= ltrim($generator->modelClass, '\\') ?> */
?>

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
