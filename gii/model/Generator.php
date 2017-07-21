<?php
namespace asinfotrack\yii2\toolbox\gii\model;

class Generator extends \yii\gii\generators\model\Generator
{

	public $iconName;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'ASi Model-Generator';
	}

	public function rules()
	{
		return array_merge(parent::rules(), [
			[['iconName'], 'match', 'pattern' => '/^[\w-]+$/', 'message' => 'Only word characters and \'-\' are allowed.'],
		]);
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'iconName'=>'FontAwesome icon name',
		]);
	}

	public function hints()
	{
		return array_merge(parent::hints(), [
			'iconName' => 'Name of the FontAwesome icon (without "fa-")',
		]);
	}

	public function generateRules($table)
	{
		$rules = parent::generateRules($table);
		foreach ($rules as &$r) {
			$r = str_replace(' => ', '=>', $r);

			$posEndAttr = strpos($r, ']', 2);
			$attrList = substr($r, 2, $posEndAttr);
			$r = substr($r, 0, 2) . str_replace(', ', ',', $attrList) . substr($r, $posEndAttr + 2);
		}
		return $rules;
	}

}
