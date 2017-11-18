<?php
namespace asinfotrack\yii2\toolbox\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This widget renders flash messages automatically. The messages can be
 * automatically retrieved from yiis session-component or be provided via
 * a custom callable.
 *
 * Example of simple usage rendering yiis session flashes each in its own
 * alert-container:
 * <code>
 *     <?= FlashMessages::widget() ?>
 * </code>
 *
 * Advanced usage with custom callback to provide flash messages:
 * <code>
 *     <?= FlashMessages::widget([
 *         'loadFlashesCallback'=>function() {
 *             return ['info'=>'Hello', 'danger'=>'World!'];
 *         },
 *     ]) ?>
 * </code>
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class FlashMessages extends \yii\bootstrap\Widget
{

	/**
	 * @var array holds all flash messages once loaded
	 */
	protected $flashes;

	/**
	 * @var \Closure optional closure with the signature 'function()' returning an
	 * array having alert-types as keys (eg danger, info, success, etc.) and the flash
	 * contents as values.
	 * If not set, the flash messages will be loaded from the session component.
	 */
	public $loadFlashesCallback;
	/**
	 * @var array options for the enclosing div container
	 */
	public $alertOptions;
	/**
	 * @var array the options for rendering the close button tag.
	 * The close button is displayed in the header of the modal window. Clicking
	 * on the button will hide the alert box. If this is false, no close button will be rendered.
	 *
	 * The following special options are supported:
	 *
	 * - tag: string, the tag name of the button. Defaults to 'button'.
	 * - label: string, the label of the button. Defaults to '&times;'.
	 *
	 * The rest of the options will be rendered as the HTML attributes of the button tag.
	 * Please refer to the [Alert documentation](http://getbootstrap.com/components/#alerts)
	 * for the supported HTML attributes.
	 */
	public $closeButton = [];
	/**
	 * @var bool whether or not to render only the first flash message. If set to false
	 * (default) all existing messages will be rendered, each within its own alert box.
	 */
	public $firstOnly = false;

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		//get flashes and return if there are none, otherwise init options
		$this->flashes = $this->loadFlashes();
		if (empty($this->flashes)) {
			if (YII_DEBUG) echo '<!-- FlashMessages: no flashes found -->';
			return;
		} else {
			$this->initOptions();
		}

		//register plugin if necessary
		if ($this->closeButton !== false) {
			$this->registerPlugin('alert');
		}

		//render
		echo Html::beginTag('div', ['class'=>'widget-flash-messages']) . "\n";
		foreach ($this->flashes as $type=>$content) {
			echo $this->renderAlertBox($type, $content);
			if ($this->firstOnly) break;
		}
		echo Html::endTag('div');
	}

	/**
	 * Populates the internal var with flash messages either retrieved via
	 * session or custom callback.
	 *
	 * @return array an array containing the flash message data
	 */
	protected function loadFlashes()
	{
		if (isset($this->loadFlashesCallback) && $this->loadFlashesCallback instanceof \Closure) {
			return call_user_func($this->loadFlashesCallback);
		} else {
			return Yii::$app->session->getAllFlashes();
		}
	}

	/**
	 * Creates an alert box
	 *
	 * @param string $type the flash-type (eg success, danger, etc.)
	 * @param string $content the content of the flash message
	 * @return string the html code
	 */
	protected function renderAlertBox($type, $content)
	{
		//options
		$options = $this->alertOptions;
		Html::addCssClass($options, 'alert-' . $type);

		//start container
		$ret = Html::beginTag('div', $options);

		//close button
		if ($this->closeButton !== false) {
			$ret .= $this->renderCloseButton();
		}

		//content
		$ret .= $content;

		//end container
		return $ret .  Html::endTag('div') . "\n";
	}

	/**
	 * Renders the close button.
	 *
	 * @return string the html code for the close button
	 */
	protected function renderCloseButton()
	{
		$closeButton = $this->closeButton;
		$tag = ArrayHelper::remove($closeButton, 'tag', 'button');
		$label = ArrayHelper::remove($closeButton, 'label', '&times;');
		if ($tag === 'button' && !isset($closeButton['type'])) {
			$closeButton['type'] = 'button';
		}

		return Html::tag($tag, $label, $closeButton);
	}

	/**
	 * Initializes the widget options.
	 * This method sets the default values for various options.
	 */
	protected function initOptions()
	{
		//alert container
		Html::addCssClass($this->alertOptions, ['alert', 'fade', 'in']);

		//close button
		if ($this->closeButton !== false) {
			$this->closeButton = array_merge([
				'data-dismiss' => 'alert',
				'aria-hidden' => 'true',
				'class' => 'close',
			], $this->closeButton);
		}
	}

}
