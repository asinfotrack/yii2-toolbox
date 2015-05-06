<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace asinfotrack\yii2\toolbox\gii\model;

use Yii;
use yii\gii\CodeFile;

class Generator extends \yii\gii\generators\model\Generator
{

	public $queryClass;
	public $iconName;
		
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['queryClass'], 'required'],
			[['queryClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['iconName'], 'match', 'pattern' => '/^[\w-]+$/', 'message' => 'Only word characters and \'-\' are allowed.'],
		]);
	}
	
	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'queryClass'=>'Query Class',
			'iconName'=>'Font-Awesome icon-name',
		]);
	}
	
	public function hints()
	{
		return array_merge(parent::hints(), [
			'queryClass'=>'This is the query class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
			'iconName' => 'Name of the font-awesome icon (without "fa-")',
		]);
	}
	
	public function requiredTemplates()
	{
		return array_merge(parent::requiredTemplates(), ['query.php']);
	}
	
	public function generate()
	{
		$queryFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->queryClass, '\\') . '.php'));
		
		return array_merge(parent::generate(), [
			new CodeFile($queryFile, $this->render('query.php')),
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
