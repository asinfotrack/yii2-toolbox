<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $form \yii\widgets\ActiveForm */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

echo Html::beginTag('fieldset');
echo Html::tag('legend', 'Custom');
echo $form->field($generator, 'viewBaseClass')->textInput(['placeholder'=>'e.g. "\app\components\View"']);
echo Html::endTag('fieldset');


echo Html::beginTag('fieldset');
echo Html::tag('legend', 'Standard');
require(Yii::getAlias('@vendor/yiisoft/yii2-gii/generators/crud/form.php'));
echo Html::endTag('fieldset');
