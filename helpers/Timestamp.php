<?php
namespace asinfotrack\yii2\toolbox\helpers;

use yii\base\InvalidParamException;
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
	 * Gets todays timestamp without the time part (time will be
	 * set to 00:00:00).
	 * 
	 * @return int timestamp
	 */
	public static function getTodayStampWithoutTime()
	{
		return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
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
		$day = date('d', $stamp);
		$month = date('m', $stamp);
		$year = date('Y', $stamp);
		
		return mktime(0, 0, 0, $month, $day, $year);
	}
	
	/**
	 * Checks whether or not a timestamp has a time part. The function returns true, if
	 * the time-part of a stamp differs from the values defined in params 2 to 4.
	 * 
	 * Default setting for no time part is 00:00:00.
	 * 
	 * @param integer $stamp the timestamp to check
	 * @param integer $noTimeHour the hour considered as no time part
	 * @param number $noTimeMinute the minute considered as no time part
	 * @param number $noTimeSecond the second considered as no time part
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
	
}
