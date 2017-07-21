<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo Html::beginTag('div', ['class'=>'page-header']);
echo Html::tag('h2', 'Custom', ['class'=>'h3']);
echo Html::endTag('div');

echo $form->field($generator, 'iconName')->textInput(['placeholder'=>'e.g. "calendar"']);

echo Html::beginTag('div', ['class'=>'page-header']);
echo Html::tag('h2', 'Standard', ['class'=>'h3']);
echo Html::endTag('div');

require(Yii::getAlias('@vendor/yiisoft/yii2-gii/generators/model/form.php'));
