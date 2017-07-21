<?php
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\model\Generator */

$queryClass = StringHelper::basename($generator->queryClass);

echo "<?php\n";
?>
namespace <?= $generator->queryNs ?>;

class <?= $queryClass ?> extends <?= '\\' . $generator->queryBaseClass . "\n" ?>
{

	/**
	 * @inheritdoc
	 */
	public function prepare($builder)
	{
		//default ordering
		if (empty($this->orderBy)) {
			//add default ordering scope here if desired
		}

		return parent::prepare($builder);
	}

}
