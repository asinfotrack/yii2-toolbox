<?php
namespace asinfotrack\yii2\toolbox\gii\crud;

use Yii;
use yii\gii\CodeFile;

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
    
    /**
     * Returns the view base class
     * @return string base class 
     */
    public function getViewBaseClass()
    {
    	return 'yii\\web\\View';
    }
    
    /**
     * (non-PHPdoc)
     * @see \yii\gii\generators\crud\Generator::generateSearchRules()
     */
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
    
    /**
     * (non-PHPdoc)
     * @see \yii\gii\generators\crud\Generator::generateActiveField($attribute)
     */
    public function generateActiveField($attribute)
    {
    	return str_replace(' => ', '=>', parent::generateActiveField($attribute));
    }
    
    /**
     * (non-PHPdoc)
     * @see \yii\gii\generators\crud\Generator::generate()
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            	$isPartial = substr($file, 0, 1) == '_';
                $files[] = new CodeFile($isPartial ? "$viewPath/partials/$file" : "$viewPath/$file", $this->render("views/$file"));
            }
        }

        return $files;
    }
    
}
