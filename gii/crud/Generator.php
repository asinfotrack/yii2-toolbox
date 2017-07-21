<?php
namespace asinfotrack\yii2\toolbox\gii\crud;

use Yii;
use yii\gii\CodeFile;

class Generator extends \yii\gii\generators\crud\Generator
{

	/**
	 * @var string the base class to use for the comments referring to `$this` within view files
	 */
	public $viewBaseClass = '\yii\web\View';
	
	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'ASi CRUD-Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'ASi Custom generator using internal code guidelines to create CRUD files';
	}

	public function rules()
	{
		return array_merge(parent::rules(), [
			[['viewBaseClass'], 'required'],
			[['viewBaseClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['viewBaseClass'], 'validateClass', 'params'=>['extends'=>\yii\web\View::className()]],
		]);
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), [
			'viewBaseClass'=>'The base class of view files to use in comments within view files',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array_merge(parent::stickyAttributes(), ['viewBaseClass']);
	}

	/**
	 * @inheritdoc
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
				$isPartial = strcmp($file[0], '_') === 0;
				$files[] = new CodeFile($isPartial ? "$viewPath/partials/$file" : "$viewPath/$file", $this->render("views/$file"));
			}
		}

		return $files;
	}

	/**
	 * @inheritdoc
	 */
	public function generateSearchRules()
	{
		//fetch rules from original implementation
		$rules = parent::generateSearchRules();

		//apply custom asi formatting
		foreach ($rules as &$r) {
			$r = str_replace(' => ', '=>', $r);

			$posEndAttr = strpos($r, ']', 2);
			$attrList = substr($r, 2, $posEndAttr);
			$r = substr($r, 0, 2) . str_replace(', ', ',', $attrList) . substr($r, $posEndAttr + 2);
		}

		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function generateActiveField($attribute)
	{
		return str_replace(' => ', '=>', parent::generateActiveField($attribute));
	}

}
