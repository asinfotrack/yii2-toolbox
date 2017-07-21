<?php
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View  */
/* @var $generator \asinfotrack\yii2\toolbox\gii\model\Generator */
/* @var $tableName string*/
/* @var $className string */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

$queryClass = StringHelper::basename($generator->queryClass);

//reorder relations
ksort($relations);
$createdRelation = ArrayHelper::remove($relations, 'CreatedBy');
if ($createdRelation !== null) $relations['CreatedBy'] = $createdRelation;
$updatedRelation = ArrayHelper::remove($relations, 'UpdatedBy');
if ($updatedRelation !== null) $relations['UpdatedBy'] = $updatedRelation;

echo "<?php\n";
?>
namespace <?= $generator->ns ?>;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use <?= $generator->queryNs . '\\' . $generator->queryClass ?>;

/**
 * This is the model class for table "<?= $tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '<?= $tableName ?>';
	}

<?php if (true && !empty($generator->iconName)): ?>
	/**
	 * Returns the font-awesome icon name assigned to this model
	 *
	 * @return string name of the icon
	 */
	public static function iconName()
	{
		return '<?= $generator->iconName ?>';
	}

<?php endif; ?>
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'timestamp'=>[
				'class'=>TimestampBehavior::className(),
				'createdAtAttribute'=>'created',
				'updatedAtAttribute'=>'updated',
			],
			'blameable'=>[
				'class'=>BlameableBehavior::className(),
				'createdByAttribute'=>'created_by',
				'updatedByAttribute'=>'updated_by',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			<?= implode(",\n			", $rules) . ',' ?>
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
	<?php foreach ($labels as $name => $label): ?>
		<?= "'$name'=>" . $generator->generateString($label) . ",\n" ?>
	<?php endforeach; ?>
		];
	}
<?php if ($generator->generateQuery): ?>

	/**
	 * Returns an instance of the query-type for this model
	 *
	 * @return <?= $generator->queryNs . '\\' . $generator->queryClass ?>;
	 */
	public static function find()
	{
		return new <?= $queryClass ?>(get_called_class());
	}
<?php endif; ?>
<?php foreach ($relations as $name => $relation): ?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?= $name ?>()
	{
		<?= str_replace(' => ', '=>', $relation[0]) . "\n" ?>
	}
<?php endforeach; ?>

}
