<?php
namespace asinfotrack\yii2\toolbox\gii\crud;

class Generator extends \yii\gii\generators\crud\Generator
{
	    
	public $moduleID;
	
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ASi CRUD-Generator';
    }
    
    public function getViewBaseClass()
    {
    	return 'yii\\web\\View';
    }
    
    public function generateSearchRules()
    {
    	$rules = parent::generateSearchRules();
    	foreach ($rules as &$r) {
    		$r = str_replace(' => ', '=>', $r);
    			
    		$posEndAttr = strpos($r, ']', 2);
    		$attrList = substr($r, 2, $posEndAttr);
    		$r = substr($r, 0, 2) . str_replace(', ', ',', $attrList) . substr($r, $posEndAttr + 2);
    	}
    	return $rules;
    }
    
    public function generateActiveField($attribute)
    {
    	return str_replace(' => ', '=>', parent::generateActiveField($attribute));
    }
    
}
