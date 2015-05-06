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

/* @var $this <?= $generator->getViewBaseClass(); ?> */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = 'Create new ' . <?= $generator->generateString('{modelClass}', ['modelClass' => Inflector::camel2words(StringHelper::basename($generator->modelClass))]) ?>;
?>

<?= "<?= " ?>$this->render('partials/_form', [
	'model'=>$model,
]); ?>
