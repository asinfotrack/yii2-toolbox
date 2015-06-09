<?php
namespace asinfotrack\yii2\toolbox\helpers;

use yii\db\Query;
use yii\base\InvalidConfigException;

/**
 * Helper class to work with migrations
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class MigrationHelper
{
	
	protected static $MIGRATION_CACHE;
	
	/**
	 * Checks whether or not a migration was applied or not
	 * 
	 * @param string $migrationName name of the migration to look for
	 * @param string $dbCon name of db connection (defaults to 'db')
	 * @param string $migrationTable name of the migration table (defaults to 'migration')
	 * @return boolean true if migration is applied or false if not
	 * @throws InvalidConfigException if there is no db-connection or the migration table does not exist
	 */
	public static function hasMigration($migrationName)
	{		
		if (!static::cacheMigrations()) {
			throw new InvalidConfigException(Yii::t('app', 'There is no valid db-connection or the migration table does not exist'));
		}
		return isset(static::$MIGRATION_CACHE[$migrationName]);
	}
	
	/**
	 * Caches the migrations internally in a static var for
	 * faster access in subsequent calls
	 * 
	 * @return boolean true if caching was successful
	 */
	protected static function cacheMigrations()
	{
		//check if already cached
		if (static::$MIGRATION_CACHE !== null) return true;
		
		//check if there is a connection
		if (!$this->hasDbConnection($dbCon) || Yii::$app->db->schema->getTableSchema('{{%migration}}') === null) {
			return false;
		}
		
		//load the data
		static::$MIGRATION_CACHE = [];
		$migrationData = (new Query())
			->select(['version','apply_time'])
			->from('{{%migration}}')
			->orderBy(['apply_time'=>SORT_ASC])
			->all();
		
		//fill the cache
		foreach ($migrationData as $migration) {
			static::$MIGRATION_CACHE[$migration['version']] = $migration['apply_time'];
		}
		
		return true;
	}
	
	/**
	 * Returns true if the db-connection is configured and established
	 * 
	 * @param string $dbCon name of db connection (defaults to 'db')
	 * @return boolean true if connected
	 */
	protected static function hasDbConnection()
	{
		return isset(Yii::$app->db) && Yii::$app->db->isActive;
	}
	
}