<?php
namespace asinfotrack\yii2\toolbox\helpers;

/**
 * Helper class to work with coordinates and coordinate systems
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Coordinates
{

	public static function distanceHaversine($lat1, $lon1, $lat2, $lon2) 
	{
		$earthRadius = 63711000;
		$dLat = deg2rad($lat2 - $lat1);
		$dLong = deg2rad($lon2 - $lon1);

		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLong / 2) * sin($dLong / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$d = $earthRadius * $c;

		return $d;
	}

}
