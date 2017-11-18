<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Helper class to work with google geocoding api's. It enables you to do one-call forward
 * and reverse geocoding. Make sure you define your api-key in the Yii-params with index
 * `api.google.key` or provide it with each call.
 *
 * To use your custom urls for the call, you can specify them in your params with the
 * indexes:
 *
 * - forward geocoding: `api.google.geocode.forward.url`
 * - reverse geocoding: `api.google.geocode.reverse.url`
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Geocoding
{

	/**
	 * @var string holds the forward-geocoding url with the placeholders for the api-key and the address.
	 */
	protected static $DEFAULT_FORWARD_URL = 'https://maps.googleapis.com/maps/api/geocode/json?key={apikey}&address={address}';

	/**
	 * @var string holds the reverse-geocoding url with the placeholders for the api-key and the lat/lng.
	 */
	protected static $DEFAULT_REVERSE_URL = 'https://maps.googleapis.com/maps/api/geocode/json?key={apikey}&latlng={latlng}';

	/**
	 * Tries to geocode an address into WGS84 latitude and longitude coordinates.
	 *
	 * If successful the method will return an array in the following format:
	 * ```php
	 * [
	 *     'latitude'=>46.2213,
	 *     'longitude'=>26.1654,
	 * ]
	 * ```
	 *
	 * If the request failed, the method will return false. The full documentation of the
	 * google api can be found here:
	 *
	 * @see https://developers.google.com/maps/documentation/geocoding/intro
	 *
	 * @param string $address the address to geocode
	 * @param int $timeout optional timeout (defaults to 5s)
	 * @param string $apiKey optional specific api key
	 * @param string $url optional custom url, make sure you use the placeholders `{address}` and `{apikey}`
	 * @return array|bool either an array containing lat and long or false upon failure
	 * @throws \Exception when not properly configured or upon problem during the api call
	 */
	public static function geocode($address, $fullResults=false, $timeout=5, $apiKey=null, $url=null)
	{
		//prepare the url by replacing the placeholders
		$preparedUrl = static::getForwardUrl($address, $apiKey, $url);

		//perform api call
		$response = static::callApi($preparedUrl, $timeout);
		if ($response === false) return false;

		//parse result and act according to status
		$data = Json::decode($response);
		if (strcasecmp($data['status'], 'ok') === 0 && count($data['results']) > 0) {
			if ($fullResults) {
				return $data['results'];
			} else {
				return [
					'latitude'=>$data['results'][0]['geometry']['location']['lat'],
					'longitude'=>$data['results'][0]['geometry']['location']['lng'],
				];
			}
		} else {
			return false;
		}
	}

	/**
	 * Tries to reverse geocode WGS84 coordinates into actual addresses and locations. The
	 * returned results depend on your choice of `$fullResults`. If set to true, the full data
	 * will be returned per result. If set to false, only the formatted addresses will be returned.
	 *
	 * Check out the link to see the difference:
	 * @see https://developers.google.com/maps/documentation/geocoding/intro#reverse-example
	 *
	 * @param float $latitude the latitude
	 * @param float $longitude the longitude
	 * @param bool $fullResults whether or not to return full results or only formatted addresses
	 * @param int $timeout optional timeout (defaults to 5s)
	 * @param string $apiKey optional specific api key
	 * @param string $url optional custom url, make sure you use the placeholders `{address}` and `{apikey}`
	 * @return array|string|bool either an array containing the results, a string with the single result
	 * (if `$fullResults` is false) or false upon failure
	 * @throws \Exception when not properly configured or upon problem during the api call
	 */
	public static function reverseGeocode($latitude, $longitude, $fullResults=false, $timeout=5, $apiKey=null, $url=null)
	{
		//prepare the url by replacing the placeholders
		$preparedUrl = static::getReverseUrl($latitude, $longitude, $apiKey, $url);

		//perform api call
		$response = static::callApi($preparedUrl, $timeout);
		if ($response === false) return false;

		//parse result and act according to status
		$data = Json::decode($response);
		if (strcasecmp($data['status'], 'ok') === 0 && count($data['results']) > 0) {
			if ($fullResults) {
				return $data['results'];
			} else {
				return $data['results'][0]['formatted_address'];
			}
		} else {
			return false;
		}
	}

	/**
	 * Does an actual api call via curl
	 *
	 * @param string $url the url to call
	 * @param integer $timeout the timeout
	 * @return mixed the raw result
	 * @throws \Exception when something goes wrong during the curl call
	 */
	protected static function callApi($url, $timeout)
	{
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

			$response = curl_exec($ch);
		} catch (\Exception $e) {
			curl_close($ch);
			Yii::error($e->getMessage());
			throw $e;
		}

		curl_close($ch);
		return $response;
	}

	/**
	 * Creates the url for forward geocoding requests
	 *
	 * @param string $address the address to lookup
	 * @param string $apiKey the api key or null for fetching via params
	 * @param string $customUrl the url or null for default
	 * @return string the prepared url
	 * @throws \yii\base\InvalidConfigException if params are invalid
	 */
	protected static function getForwardUrl($address, $apiKey=null, $customUrl=null)
	{
		//get url
		if ($customUrl !== null) {
			$url = $customUrl;
		} else if (isset(Yii::$app->params['api.google.geocode.forward.url'])) {
			$url = Yii::$app->params['api.google.geocode.forward.url'];
		} else {
			$url = static::$DEFAULT_FORWARD_URL;
		}

		//parse and return
		return str_replace(['{address}', '{apikey}'], [urlencode($address), static::getApiKey($apiKey)], $url);
	}

	/**
	 * Creates the url for forward geocoding requests
	 *
	 * @param string $latitude the address to lookup
	 * @param string $longitude the address to lookup
	 * @param string $apiKey the api key or null for fetching via params
	 * @param string $customUrl the url or null for default
	 * @return string the prepared url
	 * @throws \yii\base\InvalidConfigException if params are invalid
	 */
	protected static function getReverseUrl($latitude, $longitude, $apiKey=null, $customUrl=null)
	{
		//get url
		if ($customUrl !== null) {
			$url = $customUrl;
		} else if (isset(Yii::$app->params['api.google.geocode.reverse.url'])) {
			$url = Yii::$app->params['api.google.geocode.reverse.url'];
		} else {
			$url = static::$DEFAULT_REVERSE_URL;
		}

		//parse and return
		$latlng = $latitude . ',' . $longitude;
		return str_replace(['{latlng}', '{apikey}'], [$latlng, static::getApiKey($apiKey)], $url);
	}

	/**
	 * Configures the class for further usage
	 *
	 * @param string $customApiKey api key data or null to lookup in params
	 * @return null if no api key is set in the params
	 * @throws \yii\base\InvalidConfigException if no api key is set in the params
	 */
	protected static function getApiKey($customApiKey=null)
	{
		//check if key was provided
		if ($customApiKey !== null) return $customApiKey;

		//otherwise lookup the key in the params or throw exception
		if (!isset(Yii::$app->params['api.google.key'])) {
			$msg = Yii::t('app', 'You need to set the google api key into your params (index: `api.google.key`)');
			throw new InvalidConfigException($msg);
		}

		return Yii::$app->params['api.google.key'];
	}

}
