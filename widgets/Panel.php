<?php
namespace asinfotrack\widgets;

use yii\helpers\Html;
use yii\base\InvalidConfigException;

/**
 * This Widget renders a bootstrap-panel. Use it with its begin() and
 * end() methods while putting the body-contents in between those calls
 * or setting it via the body attribute.
 * 
 * Possible usage:
 * <code>
 * 		<?php Panel::begin(['heading'=>Html::tag('h3', 'Welcome!')]); ?>
 * 			<p>Hello world! This is a simple panel with a heading.</p>
 * 		<?php Penel::end(); ?>
 * </code>
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Panel extends \yii\base\Widget
{
	
	const PANEL_DEFAULT = 'panel-default';
	const PANEL_PRIMARY = 'panel-primary';
	const PANEL_SUCCESS = 'panel-success';
	const PANEL_INFO	= 'panel-info';
	const PANEL_WARNING	= 'panel-warning';
	const PANEL_DANGER	= 'panel-danger';
	
	protected static $PANEL_TYPES = [
		self::PANEL_DEFAULT, self::PANEL_PRIMARY, self::PANEL_SUCCESS, 
		self::PANEL_INFO, self::PANEL_WARNING, self::PANEL_DANGER,
	];
	
	/**
	 * @var string|\Closure either a string or closure containing/returning
	 * heading content. The closure has no params and should return a string.
	 */
	public $heading;
	
	/**
	 * @var string|\Closure either a string or closure containing/returning
	 * body content. The closure has no params and should return a string.
	 */
	public $body;
	
	/**
	 * @var string|\Closure either a string or closure containing/returning
	 * footer content. The closure has no params and should return a string.
	 */
	public $footer;
	
	/**
	 * @var string the panel type to use (eg 'panel-default'). Use constants
	 * of this class for this. If not set defaults to 'panel-default'.
	 */
	public $type;
	
	/**
	 * @var array html options for the wrapping panel tag
	 */
	public $options = [];
	
	/**
	 * @var array html options for the heading container
	 */
	public $headingOptions = [];
	
	/**
	 * @var array html options for the body container
	 */
	public $bodyOptions = [];
	
	/**
	 * @var array html options for the footer container
	 */
	public $footerOptions = [];
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Object::init()
	 */
	public function init()
	{
		parent::init();
		
		//preconfigure panel options
		$this->options['id'] = $this->getId();
		Html::addCssClass($this->options, 'panel');
		Html::addCssClass($this->options, empty($this->type) ? self::PANEL_DEFAULT : $this->type);
		
		//open panel
		echo Html::beginTag('div', $this->options);
		
		//heading
		if (!empty($this->heading)) {
			Html::addCssClass($this->headingOptions, 'panel-heading');
			echo Html::tag('div', $this->renderVar('heading'), $this->headingOptions);
		}
		
		//body
		Html::addCssClass($this->bodyOptions, 'panel-body');
		echo Html::beginTag('div', $this->bodyOptions);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\base\Widget::run()
	 */
	public function run()
	{
		//render body via var if necessary
		if (!empty($this->body)) {
			echo $this->renderVar('body');
		}
		
		//end body
		echo Html::endTag('div');
		
		//footer
		if (!empty($this->footer)) {
			Html::addCssClass($this->footerOptions, 'panel-footer');
			echo Html::tag('div', $this->renderVar('footer'), $this->footerOptions);
		}		
		
		//close panel
		echo Html::endTag('div');
	}
	
	/**
	 * Returns contents of one of the content vars (heading, body, footer) either
	 * directly (if it is a string) or via calling its closure
	 * @param string $varName name of the content var to render / return
	 * @return string rendered content
	 */
	protected function renderVar($varName)
	{		
		//return either string or rendered value
		if ($this->{$varName} instanceof \Closure) {
			return call_user_func($this->{$varName});
		} else {
			return $this->{$varName};
		}
	}
	
}