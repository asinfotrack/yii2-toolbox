<?php
namespace asinfotrack\yii2\toolbox\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Widget to render video tags without much overhead. Multiple sources can be specified and the
 * mime-type will be determined automatically
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Video extends \yii\base\Widget
{

	/**
	 * @var array an array of arrays/strings referencing the video sources. Those entries
	 * will be resolved with the `Url::to()`-function. Set the values according to the helpers method.
	 * @see `BaseUrl::to()`
	 *
	 * The types will be determined according to the extension.
	 */
	public $urls = [];

	/**
	 * @var integer|string width attribute for the video tag
	 */
	public $width;

	/**
	 * @var integer|string height attribute for the video tag
	 */
	public $height;

	/**
	 * @var array|string the parameter to be used to generate a valid URL for the poster
	 * @see `BaseUrl::to()`
	 */
	public $poster;

	/**
	 * @var string optionally one of the valid preload values (auto|metadata|none)
	 * @see https://www.w3schools.com/tags/att_video_preload.asp
	 */
	public $preload;

	/**
	 * @var bool whether or not to show video controls
	 */
	public $controls = true;

	/**
	 * @var bool whether or not to autoplay the video
	 */
	public $autoplay = false;

	/**
	 * @var bool whether or not to mute the video
	 */
	public $muted = false;

	/**
	 * @var bool whether or not to loop the video
	 */
	public $loop = false;

	/**
	 * @var array additional options for the video tag
	 */
	public $options = [];

	/**
	 * @var array additional options for the source tags
	 */
	public $sourceOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//validate
		if (empty($this->urls)) {
			throw new InvalidConfigException('At least one url must be set!');
		}

		//options
		$this->options['id'] = $this->id;
		if ($this->width !== null) $this->options['width'] = $this->width;
		if ($this->height !== null) $this->options['height'] = $this->height;
		if (!$this->poster !== null) $this->options['poster'] = Url::to($this->poster);
		if ($this->preload !== null) $this->options['preload'] = $this->preload;
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		//create and edit opening tag
		$openingTag = Html::beginTag('video', $this->options);
		if ($this->controls) $openingTag = str_replace('>', ' controls>', $openingTag);
		if ($this->autoplay) $openingTag = str_replace('>', ' autoplay>', $openingTag);
		if ($this->muted) $openingTag = str_replace('>', ' muted>', $openingTag);
		if ($this->loop) $openingTag = str_replace('>', ' loop>', $openingTag);
		echo $openingTag;

		//video sources
		foreach ($this->urls as $url) {
			$urlFinal = Url::to($url);

			echo Html::tag('source', '', ArrayHelper::merge($this->sourceOptions, [
				'src'=>$urlFinal,
				'type'=>FileHelper::getMimeTypeByExtension(StringHelper::basename($urlFinal)),
			]));
		}

		//closing tag
		echo Html::endTag('video');
	}

}
