<?php
namespace asinfotrack\yii2\toolbox\console;

use yii\db\Schema;

/**
 * An extended migration class simplifying certain repeating tasks while creating 
 * tables, etc.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
abstract class Migration extends \yii\db\Migration
{
	
	/**
     * Builds and executes a SQL statement for creating a new DB table. This method also
     * creates the fields and relations needed for audit trail functionality.
     *
     * The columns in the new  table should be specified as name-definition pairs (e.g. 'name' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     *
     * The [[QueryBuilder::getColumnType()]] method will be invoked to convert any abstract type into a physical one.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * put into the generated SQL.
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @param string $createdField field name holding the created timstamp
	 * @param string $createdByField field name holding the id of the user creating a record
	 * @param string $updatedField field name holding the updated timstamp
	 * @param string $updatedByField field name holding the id of the user updating a record
	 * @param string $userTableName user table name
	 * @param string $userIdField id field of the user table
	 * @param string $createdOnDelete fk option whend a user gets deleted
	 * @param string $updatedOnDelete fk option whend a user gets deleted
	 */
	public function createAuditedTable($table, $columns, $options=null, 
		$createdField='created', $createdByField='created_by', 
		$updatedField='updated', $updatedByField='updated_by',
		$userTableName='{{%user}}', $userIdField='id', 
		$createdOnDelete='SET NULL', $updatedOnDelete='SET NULL')
	{
		//first create the table
		$this->createTable($table, $columns, $options);
		
		//alter table and add audit fields
		$this->addColumn($table, $createdField, Schema::TYPE_INTEGER);
		$this->addColumn($table, $createdByField, Schema::TYPE_INTEGER);
		$this->addColumn($table, $updatedField, Schema::TYPE_INTEGER);
		$this->addColumn($table, $updatedByField, Schema::TYPE_INTEGER);

		//get actual table names
		$tableNameThis = $this->db->quoteSql($table);
		$tableNameUser = $this->db->quoteSql($userTableName);
		
		//create names for created and updated by relations
		$str = 'FK_%s_%s_%s';
		$fkCreatedBy = str_replace('`', '', sprintf($str, $tableNameThis, $tableNameUser, 'created'));
		$fkUpdatedBy = str_replace('`', '', sprintf($str, $tableNameThis, $tableNameUser, 'updated'));
		
		die($fkCreatedBy);
		
		//create the foreign keys
		$this->addForeignKey($fkCreatedBy, $table, $createdByField, $userTableName, $userIdField, $createdOnDelete, 'CASCADE');
		$this->addForeignKey($fkUpdatedBy, $table, $updatedByField, $userTableName, $userIdField, $updatedOnDelete, 'CASCADE');
	}
	
}