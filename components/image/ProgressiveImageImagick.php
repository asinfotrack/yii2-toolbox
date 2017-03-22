<?php
namespace asinfotrack\yii2\toolbox\components\image;

/**
 * Image driver fpr the Imagick-library ensuring that jpg-images
 * will be encoded progressively
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ProgressiveImageImagick extends \yii\image\drivers\Image_Imagick
{

	/**
	 * @inheritdoc
	 */
	protected function _do_save($file, $quality)
	{
		//if a jpg gets saved, use progressive encoding
		$pathInfo = pathinfo($file);
		if (strcasecmp($pathInfo['extension'], 'jpg') === 0) {
			$this->im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
		}

		return parent::_do_save($file, $quality);
	}

}
