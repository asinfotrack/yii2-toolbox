<?php
namespace asinfotrack\yii2\toolbox\helpers;

use IntlDateFormatter;
use DateTime;
use DateTimeZone;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\FormatConverter;

/**
 * Helper class for working with UNIX-timestamps
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Timestamp
{
	
	/**
	 * @var array holds the mapping for the intl date-formatter
	 */
	protected static $_dateFormats = [
        'short'  => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long'   => 1, // IntlDateFormatter::LONG,
        'full'   => 0, // IntlDateFormatter::FULL,
    ];

	/**
	 * Gets today's timestamp without the time part (time will be
	 * set to 00:00:00). By default the stamp of the local time is
	 * returned.
	 *
	 * @param bool $localTime if set to true (default) the stamp for the local
	 * time is returned. If false, UTC is retuned
	 * @return int timestamp
	 */
	public static function getTodayStampWithoutTime($localTime=true)
	{
		return $localTime ? mktime(0, 0, 0) : gmmktime(0, 0, 0);
	}
	
	/**
	 * Returns a corrected timestamp which consists only of the date
	 * part. Hours, minutes and seconds are set to zero.
	 * 
	 * @param int $stamp the timestamp to correct
	 * @return int the cleaned timestamp
	 */
	public static function removeTime($stamp)
	{
		return $stamp - ($stamp % 86400);
	}
	
	/**
	 * Checks whether or not a timestamp has a time part. The function returns true, if
	 * the time-part of a stamp differs from the values defined in params 2 to 4.
	 * 
	 * Default setting for no time part is 00:00:00.
	 * 
	 * @param integer $stamp the timestamp to check
	 * @param integer $noTimeHour the hour considered as no time part
	 * @param integer $noTimeMinute the minute considered as no time part
	 * @param integer $noTimeSecond the second considered as no time part
	 * @return boolean true if a time is set differing from the one defined in params 2 to 4
	 * @throws InvalidParamException if values are illegal
	 */
	public static function hasTime($stamp, $noTimeHour=0, $noTimeMinute=0, $noTimeSecond=0)
	{
		//assert no time values are ok
		if ($noTimeHour < 0 || $noTimeHour > 23 || $noTimeMinute < 0 || $noTimeMinute > 59 || $noTimeSecond < 0 || $noTimeSecond > 59) {
			throw new InvalidParamException('Wrong values for no time hour, minute or second received');
		}
		
		//get date parts and compare
		$d = getdate($stamp);
		return $d['hours'] != $noTimeHour || $d['minutes'] != $noTimeMinute || $d['seconds'] != $noTimeSecond;
	}
	
	/**
	 * Parses a date value into a UNIX-Timestamp
	 * 
	 * @param string $value string representing date
     * @param string $format the expected date format
	 * @param string $locale string the locale ID that is used to localize the date parsing.
     * This is only effective when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     * If not set, the locale of the [[\yii\base\Application::formatter|formatter]] will be used.
     * See also [[\yii\i18n\Formatter::locale]].
     * @param string $timeZone the timezone to use for parsing date and time values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     * @return integer|boolean a UNIX timestamp or `false` on failure.
	 */
	public static function parseFromDate($value, $format, $locale=null, $timeZone='UTC')
	{
		//default values
		$locale = $locale === null ? Yii::$app->language : $locale;
		
		//decide which parser to use
		if (strncmp($format, 'php:', 4) === 0) {
			$format = substr($format, 4);
		} else {
			if (ServerConfig::extIntlLoaded()) {
				return static::parseDateValueIntl($value, $format, $locale, $timeZone);
			} else {
				$format = FormatConverter::convertDateIcuToPhp($format, 'date');
			}
		}
		return static::parseDateValuePHP($value, $format, $timeZone);
	}
	
	/**
     * Parses a date value using the IntlDateFormatter::parse()
     * 
	 * @param string $value string representing date
     * @param string $format the expected date format
	 * @param string $locale
     * @param string $timeZone the timezone to use for parsing date and time values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     * @return integer|boolean a UNIX timestamp or `false` on failure.
	 */
	protected static function parseDateValueIntl($value, $format, $locale, $timeZone)
	{
		if (isset(static::$_dateFormats[$format])) {
			$formatter = new IntlDateFormatter($locale, static::$_dateFormats[$format], IntlDateFormatter::NONE, 'UTC');
		} else {
			// if no time was provided in the format string set time to 0 to get a simple date timestamp
			$hasTimeInfo = (strpbrk($format, 'ahHkKmsSA') !== false);
			$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $hasTimeInfo ? $timeZone : 'UTC', null, $format);
		}
		
		// enable strict parsing to avoid getting invalid date values
		$formatter->setLenient(false);
		
		// There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
		// See https://github.com/yiisoft/yii2/issues/5962 and https://bugs.php.net/bug.php?id=68528
		$parsePos = 0;
		$parsedDate = @$formatter->parse($value, $parsePos);		
		if ($parsedDate === false || $parsePos !== mb_strlen($value, Yii::$app ? Yii::$app->charset : 'UTF-8')) {
			return false;
		}
		
		return $parsedDate;
	}
	
	/**
     * Parses a date value using the DateTime::createFromFormat()
	 * 
	 * @param string $value string representing date
     * @param string $format the expected date format
     * @param string $timeZone the timezone to use for parsing date and time values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     * @return integer|boolean a UNIX timestamp or `false` on failure.
	 */
	protected static function parseDateValuePHP($value, $format, $timeZone)
	{
		// if no time was provided in the format string set time to 0 to get a simple date timestamp
		$hasTimeInfo = (strpbrk($format, 'HhGgis') !== false);
		
		$date = DateTime::createFromFormat($format, $value, new DateTimeZone($hasTimeInfo ? $timeZone : 'UTC'));
		$errors = DateTime::getLastErrors();
		if ($date === false || $errors['error_count'] || $errors['warning_count']) {
			return false;
		}
		
		if (!$hasTimeInfo) {
			$date->setTime(0, 0, 0);
		}
		return $date->getTimestamp();
	}
	
}
