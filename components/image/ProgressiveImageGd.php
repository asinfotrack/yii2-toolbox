<?php
namespace asinfotrack\yii2\toolbox\components\image;

/**
 * Image driver fpr the GD-library ensuring that jpg-images
 * will be encoded progressively
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ProgressiveImageGd extends \yii\image\drivers\Image_GD
{

	/**
	 * @inheritdoc
	 */
	protected function _do_save($file, $quality)
	{
		//load if not loaded already
		$this->_load_image();

		//if a jpg gets saved, use progressive encoding
		$pathInfo = pathinfo($file);
		if (strcasecmp($pathInfo['extension'], 'jpg') === 0) {
			imageinterlace($this->_image, true);
		}

		return parent::_do_save($file, $quality);
	}

}
