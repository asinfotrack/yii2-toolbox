<?php
namespace asinfotrack\yii2\toolbox\components;

use Yii;
use yii\base\Application;

/**
 * This URL manager implements memory-functionality for urls. This enables for example to keep
 * the state (sorting, filtering and paging) of a GridView across requests. The default configuration
 * saves this data in the session variable and appends the params to the links.
 *
 * The usage is very easy. Simply set this class as the url manager in your yii-config and specify the
 * additional attributes according to the example below and the documentation of this classes attributes.
 *
 * Example config:
 *
 * ~~~php
 * // ...
 * 'urlManager'=>[
 *     'class'=>'\asinfotrack\yii2\toolbox\components\MemoryUrlManager',
 *     'memoryMap'=>[
 *         'mycontroller'=>[
 *             'myindexaction'=>[
 *                 '/^SearchForm/',
 *                 'page'=>function() {
 *                     return rand(0,1) == 1;
 *                 },
 *             ],
 *         ],
 *     ],
 * ],
 * // ...
 * ~~~
 *
 * Each entry in the `memoryMap` can be a string representing a regex to match params to save. You can
 * optionally use the regex-rule as key and a callback returning a boolean as the value. In this case
 * the rule is only active when the callback returns true
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MemoryUrlManager extends \yii\web\UrlManager
{

	/**
	 * @var string optional prefix which will be used when generating the key for saving
	 * the data to the memory storage.
	 */
	public $memorySessionPrefix = 'urlmemory:';

	/**
	 * @var array an array mapping regex-expressions to modules, controllers and action to define
	 * what params under which conditions should be saved.
	 *
	 * The structure looks like this:
	 * ~~~php
	 * [
	 *     'mycontroller'=>[
	 *         'myindexaction'=>[
	 *             '/^SearchForm/',
	 *             'page'=>function() {
	 *                 return rand(0,1) == 1;
	 *             },
	 *         ],
	 *     ],
	 *     'mymodule'=>[
	 *         'mymodulecontroller'=>[
	 *             'mymodulecontrolleraction'=>[
	 *                 '/^MyForm/',
	 *                 'page',
	 *                 'sort',
	 *             ],
	 *          ],
	 *     ],
	 * ]
	 * ~~~
	 */
	public $memoryMap = [];

	/**
	 * @var \Closure an optional callback to implement your own saving logic for the memory.
	 * The callback should have the signature `function ($key, $data)`.
	 */
	public $saveMemoryCallback;

	/**
	 * @var \Closure an optional callback to implement your own loading logic for the memory.
	 * The callback should have the signature `function ($key)` and return either an array or null.
	 */
	public $loadMemoryCallback;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//attach handler to save memory data
		Yii::$app->on(Application::EVENT_BEFORE_ACTION, function($event) {
			if ($this->isMemoryRelevant()) $this->saveRememberedParams();
		});
	}

	/**
	 * @inheritdoc
	 */
	public function createUrl($params)
	{
		//load remembered params if necessary
		$this->loadRememberedParams($params);

		return parent::createUrl($params);
	}

	/**
	 * Checks whether or not a certain route is memory relevant
	 *
	 * @param array $memoryPath the memory path parts (eg `['site', 'index']`)
	 * @return bool true if relevant
	 */
	protected function isMemoryRelevant($memoryPath=null)
	{
		if ($memoryPath === null) $memoryPath = $this->getCurrentMemoryPath();

		//check if there is an array matching the given path
		$arr = $this->memoryMap;
		foreach ($memoryPath as $part) {
			if (!isset($arr[$part])) return false;
			$arr = $arr[$part];
		}

		return true;
	}

	/**
	 * This method is responsible for loading the params from memory storage. Params are simply
	 * added to the params provided when calling `createUrl`. If a param is already defined it has
	 * precedence over the loaded value.
	 *
	 * @param array $params the params array as provided by `createUrl`
	 */
	protected function loadRememberedParams(&$params)
	{
		//load the data
		$memoryData = $this->loadMemory($this->getMemoryKey(explode('/', trim($params[0], '/'))));
		if (empty($memoryData)) return;

		//add to params
		foreach ($memoryData as $paramName=>$paramVal) {
			if (isset($params[$paramName])) continue;
			$params[$paramName] = $paramVal;
		}
	}

	/**
	 * This method is responsible for saving the params to be remembered. It does it by comparing
	 * to the rules defined in `memoryMap`.
	 */
	protected function saveRememberedParams()
	{
		$queryParams = Yii::$app->request->queryParams;
		if (empty($queryParams)) return;

		//prepare data array
		$memoryData = [];

		//fetch rules
		$arr = $this->memoryMap;
		foreach ($this->getCurrentMemoryPath() as $part) {
			if (!isset($arr[$part])) return;
			$arr = $arr[$part];
		}

		//iterate over params
		foreach ($queryParams as $paramName=>$paramVal) {
			foreach ($arr as $key=>$val) {
				$rule = $val instanceof \Closure ? $key : $val;
				$callback = $val instanceof \Closure ? $val : null;

				//check callback if set
				if ($callback !== null) {
					if (!call_user_func($callback)) continue;
				}

				//fix incorrect specified rules for regex comparison
				if ($rule[0] !== '/') $rule = '/' . $rule;
				if ($rule[strlen($rule) - 1] !== '/') $rule = $rule . '/';

				//match the rules
				if (preg_match($rule, $paramName) !== 0) {
					$memoryData[$paramName] = $paramVal;
					break;
				}
			}
		}

		//save the data
		$this->saveMemory($this->getMemoryKey(), $memoryData);
	}

	/**
	 * Generates the key to identify the storage slot in use. Override this to generate your own
	 * storage-keys.
	 *
	 * @param array $parts optional specific parts (defaults to current request parts)
	 * @return string the key
	 */
	protected function getMemoryKey($parts=null)
	{
		return $this->memorySessionPrefix . implode('-', $parts !== null ? $parts : $this->getCurrentMemoryPath());
	}

	/**
	 * Returns an array containing module (if set), controller and action. This is used to generate
	 * the memory key and identify if a route is memory relevant.
	 *
	 * @return array array containing module (if set), controller and action ids
	 */
	protected function getCurrentMemoryPath()
	{
		$pathParts = [Yii::$app->controller->id, Yii::$app->controller->action->id];
		if (Yii::$app->module !== null) array_unshift($pathParts, Yii::$app->module->uniqueId);

		return $pathParts;
	}

	/**
	 * Loads data from the means of storage (defaults to session). Override this method or simply
	 * set the `loadMemoryCallback` if you want to change to something different than session.
	 *
	 * @param string $key the key to identify the data
	 * @return array either an array of params (paramName => paramValue) or null if nothing found
	 */
	protected function loadMemory($key)
	{
		if ($this->loadMemoryCallback !== null && $this->loadMemoryCallback instanceof \Closure) {
			return call_user_func($this->loadMemoryCallback, $key);
		} else {
			return Yii::$app->session->get($key, null);
		}
	}

	/**
	 * Saves data to the means of storage (defaults to session). Override this method or simply
	 * set the `saveMemoryCallback`if you want to change to something different than session.
	 *
	 * @param string $key the key to identify the data
	 * @param array $data the data to save (paramName => paramValue)
	 */
	protected function saveMemory($key, $data)
	{
		if ($this->saveMemoryCallback !== null && $this->saveMemoryCallback instanceof \Closure) {
			call_user_func($this->saveMemoryCallback, $key, $data);
		} else {
			Yii::$app->session->set($key, $data);
		}
	}

}
