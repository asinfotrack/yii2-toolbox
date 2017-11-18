<?php
namespace asinfotrack\yii2\toolbox\components;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Response formatter for images
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ImageResponseFormatter extends \yii\base\Component implements \yii\web\ResponseFormatterInterface
{

	const IMG_PNG = 'png';
	const IMG_JPG = 'jpg';
	const IMG_GIF = 'gif';

	/**
	 * @var array holds the allowed extensions
	 */
	protected static $ALLOWED_EXTENSIONS = [self::IMG_PNG, self::IMG_JPG, self::IMG_GIF];

	/**
	 * @var string the image extension (jpg, png, etc.) Use constants of this class as
	 * only those types are allowed
	 */
	public $extension;

	/**
	 * @inheritdoc
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		if (!in_array($this->extension, static::$ALLOWED_EXTENSIONS)) {
			$msg = Yii::t('app', 'Illegal extension {ext}, only these are allowed: {allowed}', [
				'ext'=>$this->extension,
				'allowed'=>implode(', ', static::$ALLOWED_EXTENSIONS),
			]);
			throw new InvalidConfigException($msg);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function format($response)
	{
		/* @var $response \yii\web\Response */

		$response->getHeaders()->set('Content-Type', 'image/' . strtolower($this->extension));
		$response->content = $response->data;
	}

}
