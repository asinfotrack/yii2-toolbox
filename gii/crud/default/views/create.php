<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

echo "<?php\n";
?>
use yii\helpers\Html;

/* @var $this \<?= ltrim($generator->viewBaseClass, '\\') ?> */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = Yii::t('app', 'Create new <?= ltrim($generator->generateString('{modelClass}', ['modelClass' => Inflector::camel2words(StringHelper::basename($generator->modelClass))]), '\'') ?>');
?>

<?= "<?= " ?>$this->render('partials/_form', [
	'model'=>$model,
]); ?>
