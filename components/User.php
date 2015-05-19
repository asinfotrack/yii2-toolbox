<?php
namespace asinfotrack\yii2\toolbox\components;

/**
 * This class extends the yii's basic user class with additional functionality
 * mainly focused on permission checking.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class User extends \yii\web\User
{
	
	/**
	 * Checks multiple permissions at once. All provided permissions are required (AND).
	 * 
     * @param string[] $permissionNames the name of the permissions (e.g. "edit post") that need access check.
     * @param array $params optional array containing of permission names as indexes and their params in array- 
     * form as values.
     * Each value is a name-value pairs that would be passed to the rules associated
     * with the roles and permissions assigned to the user. A param with name 'user' is added to
     * this array, which holds the value of [[id]].
     * @param boolean $allowCaching whether to allow caching the results of access checks.
     * When this parameter is true (default), if the access check of an operation was performed
     * before, its result will be directly returned when calling this method to check the same
     * operation. If this parameter is false, this method will always call
     * [[\yii\rbac\ManagerInterface::checkAccess()]] for each right to obtain the up-to-date access 
     * result. Note that this caching is effective only within the same request and only works
     * when `$params = []`.
     * @return true if all the user has all permissions
	 */
	public function canAll($permissionNames, $params=[], $allowCaching=true)
	{
		foreach ($permissionNames as $p) {
			if (!$this->can($p, isset($params[$p]) ? $params[$p] : null, $allowCaching)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Checks multiple permissions at once. Having anyone of the permissions provided is enough for this
	 * method to return true (OR).
	 *
	 * @param string[] $permissionNames the name of the permissions (e.g. "edit post") that need access check.
	 * @param array $params optional array containing of permission names as indexes and their params in array-
	 * form as values.
	 * Each value is a name-value pairs that would be passed to the rules associated
	 * with the roles and permissions assigned to the user. A param with name 'user' is added to
	 * this array, which holds the value of [[id]].
	 * @param boolean $allowCaching whether to allow caching the results of access checks.
	 * When this parameter is true (default), if the access check of an operation was performed
	 * before, its result will be directly returned when calling this method to check the same
	 * operation. If this parameter is false, this method will always call
	 * [[\yii\rbac\ManagerInterface::checkAccess()]] for each right to obtain the up-to-date access
	 * result. Note that this caching is effective only within the same request and only works
	 * when `$params = []`.
	 * @return true if the user has anyone of the provided permissions
	 */	
	public function canAny($permissionNames, $params=[], $allowCaching=true)
	{
		foreach ($permissionNames as $p) {
			if ($this->can($p, isset($params[$p]) ? $params[$p] : null, $allowCaching)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Checks multiple permissions at once. If the user has only one of the provided permissions, 
	 * this method returns true (XOR).
	 *
	 * @param string[] $permissionNames the name of the permissions (e.g. "edit post") that need access check.
	 * @param array $params optional array containing of permission names as indexes and their params in array-
	 * form as values.
	 * Each value is a name-value pairs that would be passed to the rules associated
	 * with the roles and permissions assigned to the user. A param with name 'user' is added to
	 * this array, which holds the value of [[id]].
	 * @param boolean $allowCaching whether to allow caching the results of access checks.
	 * When this parameter is true (default), if the access check of an operation was performed
	 * before, its result will be directly returned when calling this method to check the same
	 * operation. If this parameter is false, this method will always call
	 * [[\yii\rbac\ManagerInterface::checkAccess()]] for each right to obtain the up-to-date access
	 * result. Note that this caching is effective only within the same request and only works
	 * when `$params = []`.
	 * @return true if the user has exactly one of the provided permissions
	 */	
	public function canAny($permissionNames, $params=[], $allowCaching=true)
	{
		$foundOne = false;
		foreach ($permissionNames as $p) {
			if ($this->can($p, isset($params[$p]) ? $params[$p] : null, $allowCaching)) {
				if ($foundOne) return false;
				$foundOne = true;
			}
		}
		return $foundOne;
	}
	
}