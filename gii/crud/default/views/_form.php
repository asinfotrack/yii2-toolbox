<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */
/* @var $model \yii\db\ActiveRecord */

$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
	$safeAttributes = $model->attributes();
}

echo "<?php\n";
?>
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \<?= ltrim($generator->viewBaseClass, '\\') ?> */
/* @var $form \yii\widgets\ActiveForm */
/* @var $model \<?= ltrim($generator->modelClass, '\\') ?> */
?>
<?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?= "<?= " ?>$form->errorSummary($model); ?>

<?php foreach ($safeAttributes as $attribute): ?>
<?= sprintf("<?= %s ?>\n", $generator->generateActiveField($attribute)) ?>
<?php endforeach; ?>

<hr/>

<div class="form-group">
	<?= "<?= " ?>Html::submitButton(Yii::t('yii', $model->isNewRecord ? 'Create' : 'Save'), [
		'class'=>$model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
	]); ?>
</div>

<?= "<?php " ?>ActiveForm::end(); ?>
