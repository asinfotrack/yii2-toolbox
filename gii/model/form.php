<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo Html::beginTag('fieldset');
	echo Html::tag('legend', 'Custom');
	echo $form->field($generator, 'iconName')->textInput(['placeholder'=>'e.g. "calendar"']);
echo Html::endTag('fieldset');


echo Html::beginTag('fieldset');
	echo Html::tag('legend', 'Standard');
	require(Yii::getAlias('@vendor/yiisoft/yii2-gii/generators/model/form.php'));
echo Html::endTag('fieldset');
