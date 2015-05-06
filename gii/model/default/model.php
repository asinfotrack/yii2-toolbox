<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name => label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name => relation declaration)
 */

$queryClass = StringHelper::basename($generator->queryClass);

echo "<?php\n";
?>
namespace <?= $generator->ns ?>;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use <?= $generator->queryClass ?>;

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
		return [<?= "\n			" . implode(",\n			", $rules) . ",\n		" ?>];
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

	/**
	 * Returns an instance of the query-type for this model
	 * @return <?= $generator->queryClass . "\n"; ?>
	 */
	public static function find()
	{
		return new <?= $queryClass ?>(get_called_class());
	}
	
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
