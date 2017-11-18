<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use asinfotrack\yii2\toolbox\components\image\ProgressiveImageGd;
use asinfotrack\yii2\toolbox\components\image\ProgressiveImageImagick;

/**
 * Factory-class to simplify creation of image driver instances
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ImageFactory
{

	/**
	 * Constant for the GD-driver-name
	 */
	const DRIVER_GD = 'GD';

	/**
	 * Constant for the Imagick-driver-name
	 */
	const DRIVER_IMAGICK = 'Imagick';

	/**
	 * @var string[] wrapper array for all the possible drivers
	 */
	public static $ALL_DRIVERS = [self::DRIVER_GD, self::DRIVER_IMAGICK];

	/**
	 * Creates an instance of the progressively encoded image driver desired
	 *
	 * @param string $path the path to the image
	 * @param string $driver the driver (defaults to GD, use class-constants)
	 * @return \asinfotrack\yii2\toolbox\components\image\ProgressiveImageGd|\asinfotrack\yii2\toolbox\components\image\ProgressiveImageImagick the instance created
	 * @throws \yii\base\InvalidCallException when called with an invalid driver
	 * @throws \yii\base\InvalidConfigException when necessary extension is not loaded
	 */
	public static function createInstance($path, $driver=self::DRIVER_GD)
	{
		//validate driver
		if (!in_array($driver, static::$ALL_DRIVERS)) {
			$msg = Yii::t('app', 'Invalid driver \'{driver}\'! Allowed drivers are: {drivers}', [
				'driver'=>$driver,
				'drivers'=>implode(', ', static::$ALL_DRIVERS)
			]);
			throw new InvalidCallException($msg);
		}

		//try to create instance
		if ($driver === self::DRIVER_GD && ServerConfig::extGdLoaded()) {
			return new ProgressiveImageGd($path);
		} else if ($driver === self::DRIVER_IMAGICK && ServerConfig::extImagickLoaded()) {
			return new ProgressiveImageImagick($path);
		} else {
			$msg = Yii::t('app', 'The requested driver is \'{driver}\' but the corresponding extension is not loaded', [
				'driver'=>$driver,
			]);
			throw new InvalidConfigException($msg);
		}
	}

}
