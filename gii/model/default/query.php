<?php


use yii\helpers\StringHelper;
/**
 * This is the template for generating CRUD query class of the specified model.
 *
 * @var yii\web\View $this
 * @var asinfotrack\gii\model\Generator $generator
 */

$queryClass = StringHelper::basename($generator->queryClass);

echo "<?php\n";
?>
namespace <?= $generator->queryNs ?>;

/**
 * Query class for <?= $generator->modelClass ?>-model
 * @see <?= '\\' . $generator->ns . '\\' . $generator->modelClass . "\n" ?>
 */
class <?= $queryClass ?> extends <?= '\\' . $generator->queryBaseClass . "\n" ?>
{

	

}
