<?php
namespace asinfotrack\yii2\toolbox\helpers;

/**
 * This helper extends the basic functionality of the Yii2-Url-helper.
 * It provides functionality to retrieve information about the currently
 * requested url, such as TLD, subdomains, etc.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Url extends \yii\helpers\Url
{

	/**
	 * @var array internal cache for pre parsed request data
	 */
	protected static $RCACHE;

	/**
	 * Caches the data for faster access in subsequent calls
	 */
	protected static function cacheReqData()
	{
		//fetch relevant vars
		$host = rtrim(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'], '/');
		$pathInfo = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

		$hostParts = array_reverse(explode('.', $host));
		$pathParts = explode('/', $pathInfo);

		static::$RCACHE = [
			'protocol'=>!empty($_SERVER['HTTPS']) ? 'https' : 'http',
			'host'=>$host,
			'uri'=>$_SERVER['REQUEST_URI'],
			'queryString'=>$_SERVER['QUERY_STRING'],
			'hostParts'=>$hostParts,
			'numParts'=>count($hostParts),
			'pathParts'=>$pathParts,
			'numPathParts'=>count($pathParts),
		];
	}

	/**
	 * Checks whether or not the request comes from the localhost
	 *
	 * @return bool true if localhost
	 */
	public static function isLocalhost()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		return in_array(static::$RCACHE['host'], ['::1', '127.0.0.1', 'localhost']);
	}

	/**
	 * This method checks whether or not the request comes from a certain ip range
	 *
	 * @param string|array $range either a range specified as a single string with asterisks (`192.168.1.*`)
	 * as placeholders, or an array containing a from and a to address (`['192.168.1.1', '192.168.1.10']`).
	 * @return bool true if in range
	 */
	public static function isInIpRange($range)
	{
		//get from and to addresses and translate them into numeric format
		if (!is_array($range)) {
			$from = ip2long(str_replace('*', '1', $range));
			$to = ip2long(str_replace('*', '255', $range));
		} else {
			$from = ip2long($range[0]);
			$to = ip2long($range[1]);
		}

		//get request ip
		$ipReq = ip2long(static::$RCACHE['host']);

		return $ipReq >= $from && $ipReq <= $to;
	}

	/**
	 * Returns the host
	 *
	 * @return string the host
	 */
	public static function getHost()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		return static::$RCACHE['host'];
	}

	/**
	 * Returns the protocol. In case of a secure connection, this is 'https', otherwise
	 * 'http'. If $widthColonAndSlashes param is true (default), the colon and slashes
	 * will be appended.
	 *
	 * @param boolean $widthColonAndSlashes if true 'http' -> 'http://'
	 * @return string the protocol (either http or https)
	 */
	public static function getProtocol($widthColonAndSlashes=true)
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		return $widthColonAndSlashes ? self::$RCACHE['protocol'] . '://' : self::$RCACHE['protocol'];
	}

	/**
	 * Returns the TLD part of the requested host name (eg 'com', 'org', etc.). If
	 * there is no tld (eg localhost), null is returned
	 *
	 * @return string|null tld or null if there is none
	 */
	public static function getTld()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		return self::$RCACHE['numParts'] > 1 ? self::$RCACHE['hostParts'][0] : null;
	}

	/**
	 * Returns the actual domain or host-name of the current request.
	 * This can either be 'yourpage.com' or 'server23' in case of a
	 * hostname
	 *
	 * @return string domain or host
	 */
	public static function getDomain()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		//decide if tld needs to be joined
		if (self::$RCACHE['numParts'] > 1) {
			return self::$RCACHE['hostParts'][1] . '.' . static::getTld();
		} else {
			return self::$RCACHE['hostParts'][0];
		}
	}

	/**
	 * Powerful method to get the full subdomain or parts of it.
	 * If no index is provided, the full subdomain will be returned:
	 * http://my.super.fast.website.com -> 'my.super.fast'
	 * If an index is provided, that specific part is returned. In the
	 * preceding example that would translate as follows:
	 * 0:	fast
	 * 1:	super
	 * 2:	my
	 *
	 * Should there be no subdomain or the index is out of range,
	 * null is returned.
	 *
	 * @param integer $index optional index to return
	 * @return null|string either full subdomain, a part of it or null
	 */
	public static function getSubdomain($index=null)
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		//if no more than two parts there is no subdomain
		if (self::$RCACHE['numParts'] < 3) return null;

		//check if certain index is requested
		if ($index === null) {
			//join all subdomain parts and return them
			return implode('.', array_reverse(array_slice(self::$RCACHE['hostParts'], 2)));
		} else {
			//check if there is such a part
			if (self::$RCACHE['numParts'] <= $index + 2) return null;
			//return it
			return self::$RCACHE['hostParts'][2 + $index];
		}
	}

	/**
	 * Checks if an url is currently active considering the whole url
	 * and also the query parts.
	 *
	 * @param string $url the url/route to compare to the one currently active.
	 * This param can be in the same formats as you would pass it to
	 * @return bool true if the link is active
	 */
	public static function isUrlActive($url)
	{
		$url = static::to($url);
		$current = static::current();

		return $current == $url;
	}

	/**
	 * Trims the slashes on an url. Whether or not the url is absolute or relative
	 * will be considered during this so that leading slashes on relative urls won't
	 * be trimmed.
	 *
	 * @param string $url the url to trim
	 * @return string the trimmed url
	 */
	public static function trimSlashes($url)
	{
		return static::isRelative($url) ? rtrim($url, '/') : trim($url, '/');
	}

}
