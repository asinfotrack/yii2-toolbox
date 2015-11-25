<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\FormatConverter;
use IntlDateFormatter;
use DateTime;
use DateTimeZone;

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
	 * Holds the name of the utc timezone
	 */
	const TIMEZONE_UTC = 'UTC';
	
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
	 * Returns the utc offset of the currently set timezone in yii config
	 * in seconds (-43200 through 50400)
	 *
	 * @param string $timezone optional timezone (if not specified current timezone is taken)
	 * @return int offset in number of seconds
	 */
	public static function utcOffset($timezone=null)
	{
		if ($timezone === null) {
			return intval(date('Z'));
		} else {
			$tzSpecified = new DateTimeZone($timezone);
			return $tzSpecified->getOffset(new DateTime('now', $tzSpecified));
		}
	}

	/**
	 * Converts a utc timestamp into local time timestamp by adding the utc
	 * offset. That way 00:02:30 in represented in UTC will be offset in a way,
	 * that the new timestamp represents the same time in the timezone specified.
	 *
	 * @param int $utcTimestamp the utc timestamp
	 * @param string $timezone the desired timezone (defaults to currently set timezone)
	 * @return int timestamp representing the same time in local time
	 */
	public static function convertUtcToLocalTime($utcTimestamp, $timezone=null)
	{
		return $utcTimestamp + static::utcOffset($timezone);
	}

	/**
	 * Gets the midnight timestamp (00:00:00) of today (default) or a day specifiable via
	 * params. If no timezone is specified, the currently set timezone via yii config will
	 * be taken
	 *
	 * @param string $timezone the desired timezone (defaults to currently set timezone)
	 * @param int $month the optional month
	 * @param int $day the optional day
	 * @param int $year the optional year
	 * @return int timestamp the midnight-timestamp in the desired timezone
	 */
	public static function getMidnightTimestamp($timezone=null, $month=null, $day=null, $year=null)
	{
		$month = $month === null ? date('m') : $month;
		$day = $day === null ? date('d') : $day;
		$year = $year === null ? date('Y') : $year;

		if (!checkdate($month, $day, $year)) {
			$msg = Yii::t('app', 'The date specified does not exist!');
			throw new InvalidParamException($msg);
		}

		$offset = static::utcOffset($timezone !== null ? $timezone : Yii::$app->timeZone);
		return gmmktime(0, 0, 0, $month, $day, $year) + $offset;
	}
	
	/**
	 * This method takes a timestamp and sets is time to midnight on the same day in the
	 * specified timezone.
	 * 
	 * @param int $timestamp the timestamp to set the time to midnight
	 * @param string $timezoneSource the timezone to calculate the day for
	 * @return int the midnight timestamp of the same timezone as timezone source
	 */
	public static function makeMidnight($timestamp, $timezoneSource=null)
	{
		$tzSource = new DateTimeZone($timezoneSource === null ? Yii::$app->timeZone : $timezoneSource);
		$dt = new DateTime();
		$dt->setTimezone($tzSource);
		$dt->setTimestamp($timestamp);
		$dt->setTime(0, 0, 0);

		return intval($dt->format('U'));
	}
	
	/**
	 * Checks whether or not a timestamp has a time part. The function returns true, if
	 * the time-part of a stamp differs from the values defined in params 2 to 4.
	 * 
	 * Default setting for no time part is 00:00:00.
	 * 
	 * @param integer $stamp the timestamp to check
	 * @param string $timezone the timezone the timestamp is in (defaults to UTC)
	 * @return boolean true if a time is set differing from the one defined in params 2 to 4
	 * @throws InvalidParamException if values are illegal
	 */
	public static function hasTime($stamp, $timezone=self::TIMEZONE_UTC)
	{
		return ($stamp + static::utcOffset($timezone)) % 86400 != 0;
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
