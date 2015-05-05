<?php
namespace asinfotrack\yii2\toolbox\helpers;

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
	
}