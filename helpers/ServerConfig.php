<?php
namespace asinfotrack\yii2\toolbox\helpers;

use yii\base\InvalidParamException;

/**
 * Helper class to get information about server-configuration and loaded
 * PHP-Modules.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ServerConfig
{
	
	const EXT_OPCACHE 	= 'Zend OPcache';
	const EXT_MEMCACHE	= 'memcache';
	const EXT_CURL 		= 'curl';
	const EXT_ODBC		= 'odbc';
	const EXT_INTL		= 'intl';
	const EXT_GD		= 'gd';
	const EXT_IMAGICK	= 'imagick';
	const EXT_OPENSSL	= 'openssl';
	
	/**
	 * Returns the version of the running php executable.
	 * @return string the version
	 */
	public static function phpVersion()
	{
		return phpversion();
	}
	
	/**
	 * Returns the path of the PHP-ini-file currently in use
	 * @return string path to ini-file
	 */
	public static function phpIniPath()
	{
		return php_ini_loaded_file();
	}
	
	/**
	 * Returns the ini-setting for max execution time
	 * @return integer number of seconds
	 */
	public static function phpMaxExecutionTime()
	{
		return intval(ini_get('max_execution_time'));
	}
	
	/**
	 * Returns the MB-value of the memory limit
	 * @param $trimUnit boolean if true, the return value will be an int without the unit ('M')
	 * @return integer|string number in MB or string containing number and unit
	 */
	public static function phpMemoryLimit($trimUnit=true)
	{
		$val = ini_get('memory_limit');
		return $trimUnit ? intval($val) : $val;
	}
	
	/**
	 * Checks whether or not an axtension is loaded via its identifier string
	 * (eg GD2 has 'gd' or MemCache has 'memcache')
	 * @param string $identifier true if loaded
	 * @return boolean
	 */
	public static function extLoaded($identifier)
	{
		return extension_loaded($identifier);
	}
	
	/**
	 * Whether or not PHP-OPcache is loaded
	 * @return boolean true if loaded
	 */
	public static function extOpCacheLoaded()
	{
		return static::extLoaded(self::EXT_OPCACHE);
	}
	
	/**
	 * Whether or not PHP-OPcache is loaded and enabled
	 * @return boolean true if loaded
	 */
	public static function opCacheEnabled()
	{
		if (!static::extOpCacheLoaded()) return false;
		$status = opcache_get_status();
		return $status['opcache_enabled'];
	}
	
	/**
	 * Whether or not memcache is loaded
	 * @return boolean true if loaded
	 */
	public static function extMemCacheLoaded()
	{
		return static::extLoaded(static::EXT_MEMCACHE);
	}
	
	/**
	 * Whether or not the curl-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extCurlLoaded()
	{
		return static::extLoaded(static::EXT_CURL);
	}
	
	/**
	 * Whether or not the odbc-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extOdbcLoaded()
	{
		return static::extLoaded(static::EXT_ODBC);
	}
	
	/**
	 * Whether or not the internationalization-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extIntlLoaded()
	{
		return static::extLoaded(static::EXT_INTL);
	}
	
	/**
	 * Whether or not the gd-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extGdLoaded()
	{
		return static::extLoaded(static::EXT_GD);
	}
	
	/**
	 * Whether or not the imagick-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extImagickLoaded()
	{
		return static::extLoaded(static::EXT_IMAGICK);
	}
	
	/**
	 * Whether or not the openSSL-extension is loaded
	 * @return boolean true if loaded
	 */
	public static function extOpenSSLLoaded()
	{
		return static::extLoaded(static::EXT_OPENSSL);
	}
	
	/**
	 * This function takes several EXT_-constants of this class combined and checks 
	 * if they are available. If the requirements are met, true is returned.
	 * 
	 * @param string|string[] $extConst either one or an array of extension constants
	 * @param boolean $requireAll if set to true, all passed extensions must be available,
	 * otherwise only one of them is enough (defaults to true). This second param is only
	 * relevant if the first param is an array
	 * @return boolean true if requirements are met
	 */
	public static function checkExtensions($extConstants, $requireAll=true)
	{
		if (is_array($extConstants)) {
			foreach ($extConstants as $ext) {
				if (static::extLoaded($ext)) {
					if (!$requireAll) return true;
				} else {
					if ($requireAll) return false;
				}
			}
			return true;
		} else {
			return static::extLoaded($extConstants);
		}
	}
	
}