<?php
namespace asinfotrack\yii2\toolbox\helpers;

/**
 * Helper class to work with rgb- and hex-color. Use it to easily
 * modify colors or translate their corresponding formats.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ColorHelper
{

	//constants for desired output format
	const FORMAT_SAME 		= 0;
	const FORMAT_RGB_ARR 	= 1;
	const FORMAT_HEX_STR 	= 2;

	/**
	 * @var string regex for hex-value validation
	 */
	protected static $HEX_REGEXP = '/^#?([a-f0-9]{3}|[a-f0-9]{6})$/';

	/**
	 * Calculate the luminance for a color
	 *
	 * @param integer[]|string $color either a hex-string or a rgb-array
	 * @return float the luminance value
	 */
	public static function calculateLuminance($color)
	{
		$rgb = static::getMixedColorAsRgb($color);
		if ($rgb === false) return false;

		return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
	}

	/**
	 * Creates a definable amount of color steps between two colors. Optionally
	 * the beginning and end colors can be included as well (default).
	 * The output will be in the same format as $colorFrom
	 *
	 * @param integer[]|string $colorFrom either a hex-string or a rgb-array
	 * @param integer[]|string $colorTo either a hex-string or a rgb-array
	 * @param integer $stepsBetween
	 * @param boolean $includeFromAndTo
	 * @return integer[][]|string[]|boolean color steps in the same format as $colorFrom
	 * or false if some params were invalid
	 */
	public static function createColorSteps($colorFrom, $colorTo, $stepsBetween, $includeFromAndTo=true)
	{
		$rgbFrom = static::getMixedColorAsRgb($colorFrom);
		$rgbTo = static::getMixedColorAsRgb($colorTo);
		if ($rgbFrom === false || $rgbTo === false) return false;

		//prepare return array
		$colors = [];

		//check if colors are the same and take shortcut if so, otherwise calculate
		if (static::areSameColors($rgbFrom, $rgbTo)) {
			for ($i=0; $i<$stepsBetween; $i++) {
				$colors[] = [$rgbFrom[0], $rgbFrom[1], $rgbTo[2]];
			}
		} else {
			$stepModifiers = [];
			for ($i=0; $i<3; $i++) {
				$stepModifiers[$i] = ($rgbTo[$i] - $rgbFrom[$i]) / ($stepsBetween + 1);
			}

			for ($i=0; $i<$stepsBetween; $i++) {
				$colors[] = [
					$rgbFrom[0] + ($i + 1) * $stepModifiers[0],
					$rgbFrom[1] + ($i + 1) * $stepModifiers[1],
					$rgbFrom[2] + ($i + 1) * $stepModifiers[2],
				];
			}
		}

		//prepare colors for return
		$ret = [];
		foreach ($colors as $color) {
			$ret[] = static::makeSameAsInput($color, $colorFrom);
		}

		//append and prepend colors if necessary
		if ($includeFromAndTo) {
			array_unshift($ret, static::makeSameAsInput($rgbFrom, $colorFrom));
			array_push($ret, static::makeSameAsInput($rgbTo, $colorFrom));
		}

		return $ret;
	}

	/**
	 * Darkens a color by a certain amount of percent. The input can be both
	 * a hex-string or a rgb-array. The output will be in the exact same format
	 *
	 * @param integer[]|string $color either a hex-string or a rgb-array
	 * @param float|float[] $percent percentage to lighten the color, either one value
	 * or three individual values
	 * @return integer[]|string|boolean false if invalid color, otherwise darkened
	 * color in the same format as the input was
	 */
	public static function darken($color, $percent)
	{
		$rgb = static::getMixedColorAsRgb($color);
		if ($rgb === false) return false;
		if (!static::validatePercent($percent)) return false;

		if (is_array($percent)) {
			foreach ($percent as &$p) $p = abs($p) * -1;
		} else {
			$percent = abs($percent) * -1;
		}

		return static::makeSameAsInput(static::modifyColor($rgb, $percent), $color);
	}

	/**
	 * Lightens a color by a certain amount of percent. The input can be both
	 * a hex-string or a rgb-array. The output will be in the exact same format
	 *
	 * @param integer[]|string $color either a hex-string or a rgb-array
	 * @param float|float[] $percent percentage to lighten the color, either one value
	 * or three individual values
	 * @return integer[]|string|boolean false if invalid color, otherwise lightened
	 * color in the same format as the input was
	 */
	public static function lighten($color, $percent)
	{
		$rgb = static::getMixedColorAsRgb($color);
		if ($rgb === false) return false;
		if (!static::validatePercent($percent)) return false;

		if (is_array($percent)) {
			foreach ($percent as $i=>$p) {
				$percent[$i] = abs($p);
			}
		} else {
			$percent = abs($percent);
		}

		return static::makeSameAsInput(static::modifyColor($rgb, $percent), $color);
	}

	/**
	 * Converts an array containing rgb-color-codes into its hex-representation.
	 * The array needs to consist of three integers containing the three color values.
	 *
	 * @param integer[] $rgbArr array containing r, g and b as integers between 0
	 * and 255
	 * @param boolean $withHash if set to true, the hex-value will be preceded by a hash ('#')
	 * @param boolean $preferThreeChars if set to true the three char representation is returned
	 * if possible
	 * @return boolean|string hex-string or false if the provided rgb-array was invalid
	 */
	public static function decToHex($rgbArr, $withHash=true, $preferThreeChars=true)
	{
		if (!static::validateRgbColor($rgbArr)) return false;

		//get parts
		$hex = '';
		foreach ($rgbArr as $color) {
			$hex .= str_pad(dechex($color), 2, 0, STR_PAD_LEFT);
		}

		//three chars if desired and possible
		if ($preferThreeChars && $hex[0] == $hex[1] && $hex[2] == $hex[3] && $hex[4] == $hex[5]) {
			$hex = $hex[0] . $hex[2] . $hex[4];
		}

		return ($withHash ? '#' : '') . $hex;
	}

	/**
	 * Converts a hex-color into an array containing the three decimal
	 * representation values of r, g, and b
	 *
	 * @param string $hexStr hex-color with either three or six chars, hash is optional
	 * @return boolean|integer[] array containing dec values for rgb or false if string
	 * is invalid
	 */
	public static function hexToRgb($hexStr)
	{
		if (!static::validateHexString($hexStr)) return false;
		$hexStr = ltrim(trim($hexStr), '#');

		//create parts and make it six digit if it isnt already
		$parts = str_split($hexStr, strlen($hexStr) == 3 ? 1 : 2);
		if (strlen($hexStr) == 3) {
			foreach ($parts as $i=>$p) {
				$parts[$i] = str_repeat($p, 2);
			}
		}

		return [hexdec($parts[0]), hexdec($parts[1]), hexdec($parts[2])];
	}

	/**
	 * Validates a hex-string to assert it is valid. The hash in front of the
	 * string is optional. Examples for valid hex-strings:
	 * #aaa, aaa, #121212, 121212
	 *
	 * @param string $str hex string
	 * @return boolean true if ok, otherwise false
	 */
	public static function validateHexString($str)
	{
		return preg_match(static::$HEX_REGEXP, $str) === 1;
	}

	/**
	 * Validates an array containing rgb-colors. The array needs to contain
	 * three integer-values, each between 0 and 255 to be valid
	 *
	 * @param integer[] $rgbArr the rgb-values
	 * @return boolean true if ok, otherwise false
	 */
	public static function validateRgbColor($rgbArr)
	{
		if (count($rgbArr) != 3) return false;
		foreach ($rgbArr as $val) {
			if ($val < 0 || $val > 255) return false;
		}
		return true;
	}

	/**
	 * Validates percentage values
	 *
	 * @param float|float[] $percent either one value or three individual values
	 * @return boolean true if ok, otherwise false
	 */
	public static function validatePercent($percent)
	{
		if (is_array($percent) && count($percent) != 3) return false;
		return true;
	}

	/**
	 * Internal modification function for colors.This method DOES NOT
	 * validate the input so make sure this happened before
	 * @param integer[] $rgbArr array containing rgb-values
	 * @param float|float[] $percent
	 * @return integer[] modified color as rgb-array
	 */
	protected static function modifyColor($rgbArr, $percent)
	{
		if (is_array($percent)) {
			foreach ($rgbArr as $i=>&$c) {
				$multiplier = $percent[$i] > 0 ? 1 + $percent[$i] / 100 : 1 - $percent[$i] / 100;
				$c = intval(round($c * $multiplier));
				if ($c > 255) $c = 255;
				if ($c < 0) $c = 0;
			}
		} else {
			$multiplier = $percent > 0 ? 1 + $percent / 100 : 1 - $percent / 100;
			foreach ($rgbArr as &$c) {
				$c = intval(round($c * $multiplier));
				if ($c > 255) $c = 255;
				if ($c < 0) $c = 0;
			}
		}

		return $rgbArr;
	}

	/**
	 * Compares two colors if they are the same. This method DOES NOT
	 * validate the input so make sure this happened before
	 *
	 * @param integer[]|string $colorOne either a hex-string or a rgb-array
	 * @param integer[]|string $colorTwo either a hex-string or a rgb-array
	 * @return boolean true if they are the same
	 */
	protected function areSameColors($colorOne, $colorTwo)
	{
		$rgbOne = static::getMixedColorAsRgb($colorOne);
		$rgbTwo = static::getMixedColorAsRgb($colorTwo);

		for ($i=0; $i<3; $i++) {
			if ($rgbOne[$i] != $rgbTwo[$i]) return false;
		}
		return true;
	}

	/**
	 * Converts an rgb-array into the same format as the input was.
	 *
	 * @param integer[] $rgbArr array containing rgb-values
	 * @param integer[]|string $input either rgb-array or hex-string
	 * @return integer[]|string color in the same format as the input was
	 */
	protected function makeSameAsInput($rgbArr, $input)
	{
		if (is_array($input)) {
			return $rgbArr;
		} else {
			return static::decToHex($rgbArr, $input[0]=='#', strlen($input) == ($input[0]=='#' ? 4 : 3));
		}
	}

	/**
	 * Takes either an rgb-array or a hex-string as its input and validates
	 * it and returns an rgb-array
	 *
	 * @param string|integer[] $color either hex-string or rgb-array
	 * @return boolean|integer[] either an rgb-array or false if color is invalid
	 */
	protected static function getMixedColorAsRgb($color)
	{
		if (is_array($color)) {
			if (!static::validateRgbColor($color)) return false;
			return $color;
		} else {
			return static::hexToRgb($color);
		}
	}

}
