<?php
namespace asinfotrack\yii2\toolbox\gii\model;

class Generator extends \yii\gii\generators\model\Generator
{

	/**
	 * @var string name of the font-awesome icon
	 */
	public $iconName;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'ASi Model-Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['iconName'], 'match', 'pattern' => '/^[\w-]+$/', 'message' => 'Only word characters and \'-\' are allowed.'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'iconName'=>'FontAwesome icon name',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array_merge(parent::hints(), [
			'iconName' => 'Name of the FontAwesome icon (without "fa-")',
		]);
	}

	/**
	 * @inheritdoc
	 */
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
