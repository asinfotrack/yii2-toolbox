<?php
namespace asinfotrack\yii2\toolbox\validators;

use Yii;
use yii\validators\RequiredValidator;
use yii\base\InvalidConfigException;

/**
 * Validator to require a certain amount of fields out of a list to be required.
 * To use the validator simply specify the selection of attributes and set how many of it are required:
 * 
 * <code>
 * public function rules()
 * {
 *     return [
 *         // ...
 *         [['phonePrivate','phoneWork','mobile'], SelectiveRequiredValidator::className(), 'errorAttribute'=>'phonePrivate'],
 *         // ...
 *     ];
 * }
 * </code>
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class SelectiveRequiredValidator extends \yii\validators\Validator
{
	
	/**
	 * @var integer the number of fields beeing required
	 */
	public $numRequired = 1;
	
	/**
	 * @var string the attribute name to add the error-message to. If not
	 * set, the message will be added to all attributes.
	 */
	public $errorAttribute;
	
	/**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{num}`: the number of values required (@see $numRequired)
     * - `{attributes}`: list of all attributes of which several (@see $numRequired) are required
     * - `{errorAttribute}`: the label of the attribute receiving the error msg (if defined)
	 */
	public $message;
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::init()
	 */
	public function init()
	{
		parent::init();
		
		//assert valid config
		if (count($this->attributes) < 2) {
			throw new InvalidConfigException('The SelectiveRequiredValidator needs at least two attributes');
		}
		if ($this->numRequired < 1 || $this->numRequired >= count($this->attributes)) {
			throw new InvalidConfigException('numRequired needs to be bigger than 0 and smaller than the number of attributes defined');
		}
				
		//prepare error message
        if ($this->message === null) {
        	$this->message = 'At least {num,plural,=1{one} other{#}} of the attributes {attributes} {num2, plural, =1{is} other{are}} required';
        }
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::validateAttributes()
	 */
	public function validateAttributes($model, $attributes=null)
	{
		//prepare attributes
		if (is_array($attributes)) {
			$attributes = array_intersect($this->attributes, $attributes);
		} else {
			$attributes = $this->attributes;
		}
		
		//validate all attributes with the required validator
		$reqVal = new RequiredValidator();
		$numOk = 0;
		$labelList = [];
		foreach ($attributes as $attr) {
			$labelList[] = $model->getAttributeLabel($attr);
			if ($reqVal->validate($model->{$attr})) $numOk++;
		}
		
		//check if ok or add error message
		if ($numOk < $this->numRequired) {
			//prepare message			
			$msg = Yii::t('yii', $this->message, [
				'num'=>$this->numRequired,
				'num2'=>$this->numRequired,
				'attributes'=>implode(', ', $labelList),
				'errorAttribute'=>$this->errorAttribute,
			]);
			
			//decide where to add the message
			if ($this->errorAttribute !== null) {
				$this->addError($model, $this->errorAttribute, $msg);
			} else {
				foreach ($this->attributes as $attr) {
					$this->addError($model, $attr, $msg);
				}
			}
		}
	}
	
}