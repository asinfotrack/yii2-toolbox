<?php
namespace asinfotrack\yii2\toolbox\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\bootstrap\Button;
use yii\bootstrap\Modal;
use yii\caching\Cache;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\JsExpression;
use yii\bootstrap\ActiveForm;
use asinfotrack\yii2\toolbox\helpers\Url as AsiUrl;
use asinfotrack\yii2\toolbox\helpers\ServerConfig;

/**
 * The debug action shows you relevant information about the current configuration of
 * the hosting. It also shows you all kind of configs right in the browser.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class DebugAction extends \yii\base\Action
{

	/**
	 * @var string holds the modal template code
	 */
	protected $modalCode;

	/**
	 * @var string holds the php info code
	 */
	protected $phpInfo;

	/**
	 * @var string the path to the view file used to render the results of this
	 * standalone action. Use the variable `$content` to render the results of this
	 * action wherever you want.
	 */
	public $view;

	/**
	 * @var string The html tag used for the section titles;
	 */
	public $sectionTitleTag = 'h2';

	/**
	 * @var string the separator used between the sections
	 */
	public $sectionSeparator = '<hr/>';

	/**
	 * @var array The options for the section tables
	 */
	public $sectionTableOptions = ['class'=>'detail-view table table-striped table-condensed table-bordered'];

	/**
	 * @var array This array defines which sections in which order are shown. The key is always used as the
	 * title of the section. The values can be:
	 *
	 * - a string: the class looks for a function with the name `sectionName()` where `name` will
	 * be replaced with the string defined in the array. There are a number of default methods available.
	 * - a callback returning an array of key/value pairs used in the section table
	 * - an array holding key/value pairs as they would be returned by the callback of the point above
	 */
	public $sections = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//assert view file is set
		if ($this->view === null) {
			$msg = Yii::t('app', 'Please set a view file');
			throw new InvalidConfigException($msg);
		}

		//default config
		if (empty($this->sections)) {
			$this->sections = [
				'Server'=>'server',
				'Yii'=>'yii',
				'Database'=>'db',
				'Localization'=>'localization',
				'Caching'=>'caching',
				'RBAC'=>'rbac',
				'Current User'=>'currentuser',
			];
		}

		//get php info
		$this->loadPhpInfo();

		//get modal code
		ob_start();
		Modal::begin(['id'=>'{id}', 'header'=>'<h4>{title}</h4>', 'size'=>Modal::SIZE_LARGE]);
		echo '{content}';
		Modal::end();
		$this->modalCode = ob_get_clean();
	}

	/**
	 * @inheritdoc
	 *
	 * @return mixed the returning result for the controller
	 */
	public function run()
	{
		//check if flushing required
		$flushValue = Yii::$app->request->post('flush-cache');
		if ($flushValue !== null) {
			$this->flushCache($flushValue);
			return Yii::$app->controller->refresh();
		}

		//sections
		$i = 0;
		ob_start();
		foreach ($this->sections as $key=>$value) {
			//render section
			if (is_string($value)) {
				$camelized = Inflector::camelize($value);
				$methodName = 'section' . $camelized;
				if (method_exists($this, $methodName)) {
					$this->renderSection($key, $this->{$methodName}());
				} else {
					$msg = Yii::t('app', 'There is no method `section{section}` defined', ['section'=>$camelized]);
					throw new InvalidConfigException($msg);
				}
			} else if ($value instanceof \Closure) {
				$this->renderSection($key, call_user_func($value));
			} else if (is_array($value)) {
				$this->renderSection($key, $value);
			} else {
				$msg = Yii::t('app', 'Invalid format of section definition {section}', ['section'=>$key]);
				throw new InvalidConfigException($msg);
			}
			$i++;

			//separator
			if (!empty($this->sectionSeparator) && $i < count($this->sections)) {
				echo $this->sectionSeparator;
			}
		}

		return $this->render(ob_get_clean());
	}

	/**
	 * Does the actual rendering of the action
	 *
	 * @param string $content the content of the view
	 * @return mixed the returning result for the controller
	 */
	protected function render($content)
	{
		return Yii::$app->controller->render($this->view, [
			'content'=>$content,
		]);
	}

	/**
	 * Renders an actual section
	 *
	 * @param string $title the title of the section
	 * @param array $contentArray key/value pairs representing titles and values of entries
	 * @return string the rendered html code
	 */
	protected function renderSection($title, $contentArray)
	{
		//title
		echo Html::tag($this->sectionTitleTag, $title);

		//table
		echo Html::beginTag('table', $this->sectionTableOptions);
		echo Html::beginTag('tbody');
		foreach ($contentArray as $key=>$value) {
			$value = $value instanceof \Closure ? call_user_func($value) : $value;

			echo Html::beginTag('tr');
			echo Html::tag('th', $key);
			echo Html::tag('td', $value);
			echo Html::endTag('tr');
		}
		echo Html::endTag('tbody');
		echo Html::endTag('table');
	}

	/**
	 * Prepares the server section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionServer()
	{
		$extCheck = [
			'Zend OPcache','memcache','apc','xcache','redis','wincache','Zend Data Cache','curl',
			'odbc','intl','gd','imagick','openssl','xdebug',
		];
		$extensions = [];
		foreach ($extCheck as $ext) {
			$hasExt = extension_loaded($ext);
			$extensions[] = Html::tag('span', $ext, ['class'=>'label label-' . ($hasExt ? 'success' : 'danger')]);
		}

		$ret = [
			'Host'=>AsiUrl::getHost(),
			'Localhost detected'=>$this->valueBool(AsiUrl::isLocalhost()),
			'PHP version'=>phpversion(),
			'PHP info'=>$this->valueModal('PHP info', $this->phpInfo),
			'Relevant extensions'=>implode(' ', $extensions),
		];
		if (ServerConfig::extOpCacheLoaded()) {
			$ret['OpCache enabled'] = $this->valueBool(ServerConfig::opCacheEnabled());
		}

		return $ret;
	}

	/**
	 * Prepares the yii section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionYii()
	{
		return [
			'Version'=>Yii::getVersion(),
			'YII_DEBUG'=>$this->valueBool(YII_DEBUG),
			'YII_ENV'=>Html::tag('code', YII_ENV),
			'YII_ENV_DEV'=>$this->valueBool(YII_ENV_DEV),
			'YII_ENV_PROD'=>$this->valueBool(YII_ENV_PROD),
		];
	}

	/**
	 * Prepares the db section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionDb()
	{
		if (Yii::$app->db === null) return ['Status'=>'not configured'];
		$db = Yii::$app->db;

		return  [
			'Active'=>$this->valueBool($db->isActive),
			'Driver name'=>$this->valueCode($db->driverName),
			'DSN'=>$this->valueCode($db->dsn),
			'Username'=>$this->valueCode($db->username),
			'Password'=>$this->valueCode($db->password),
			'Schema cache enabled'=>$this->valueBool($db->enableSchemaCache),
			'Schema cache duration'=>$db->schemaCacheDuration . 's',
			'Schema cache component'=>$this->valueCode($db->schemaCache instanceof Cache ? $db->schemaCache->className() : $db->schemaCache),
			'Schema cache config'=>$db->schemaCache instanceof Cache ? $this->valueModal($this->valueVarDump($db->schemaCache)) : $this->valueCode('@see component \'' . $db->schemaCache . '\''),
			'Query cache enabled'=>$this->valueBool($db->enableQueryCache),
			'Query cache duration'=>$db->queryCacheDuration . 's',
			'Query cache component'=>$this->valueCode($db->queryCache instanceof Cache ? $db->queryCache->className() : $db->queryCache),
			'Query cache config'=>$db->queryCache instanceof Cache ? $this->valueModal($this->valueVarDump($db->queryCache)) : $this->valueCode('@see component \'' . $db->queryCache . '\''),
		];
	}

	/**
	 * Prepares the localization section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionLocalization()
	{
		return [
			'Locale'=>Yii::$app->formatter->locale,
			'Language'=>Yii::$app->language,
			'Source lang'=>Yii::$app->sourceLanguage,
			'Timezone'=>function() {
				$dtCur = new \DateTime('now', new \DateTimeZone(Yii::$app->timeZone));
				$dtUtc = new \DateTime('now', new \DateTimeZone('UTC'));
				return Yii::$app->timeZone
					. Html::beginTag('small', ['text-muted']) . Html::tag('br')
					. 'local time: ' . $dtCur->format('d.m.Y H:i:s') . Html::tag('br')
					. 'UTC-offset: ' . $dtCur->getOffset() . 's' . Html::tag('br')
					. 'UTC time: ' . $dtUtc->format('d.m.Y H:i:s')
					. Html::endTag('small');
			},
		];
	}

	/**
	 * Prepares the caching section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionCaching()
	{
		$ret = [];
		foreach (Yii::$app->components as $id=>$component) {
			$instance = Yii::$app->{$id};
			if (!($instance instanceof Cache)) continue;

			$title = $this->valueCode($id);

			ob_start();
			ActiveForm::begin();
			echo $this->valueCode($instance->className());
			echo $this->valueModal('Cache ' . $id, $this->valueVarDump($instance), 'Show config');
			echo Html::hiddenInput('flush-cache', $id);
			echo Html::submitButton('Flush', [
				'class'=>'btn btn-xs btn-danger',
				'data'=>['confirm'=>'Are you sure?'],
			]);
			ActiveForm::end();

			$ret[$title] = ob_get_clean();
		}

		return $ret;
	}

	/**
	 * Prepares the rbac section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionRbac()
	{
		if (Yii::$app->authManager === null) return ['Status'=>'not configured'];
		$am = Yii::$app->authManager;

		//get roles and permission
		$roles = [];
		$perms = [];
		foreach ($am->roles as $role) $roles[] = $this->valueCode($role->name);
		foreach ($am->permissions as $perm) $perms[] = $this->valueCode($perm->name);

		//rbac cache config
		if ($am->cache === null) {
			$cacheConfig = Yii::$app->formatter->nullDisplay;
		} else {
			$cacheConfig = $this->valueModal('RBAC cache config', $this->valueVarDump($am->cache), 'Show');
		}

		return [
			'Auth manager'=>$this->valueCode($am->className()) . $this->valueModal($am->className(), $this->valueVarDump($am), 'Show config'),
			'Roles'=>implode(', ', $roles),
			'Permissions'=>implode(', ', $perms),
			'RBAC cache enabled'=>$this->valueBool($am->cache !== null),
			'RBAC cache config'=>$cacheConfig,
		];
	}

	/**
	 * Prepares the current user section
	 *
	 * @return array the configuration of the section
	 */
	protected function sectionCurrentUser()
	{
		$u = Yii::$app->user;
		if ($u->isGuest) return ['Is guest'=>$this->valueBool(true)];

		//basics
		$identity = Yii::$app->user->identity;
		$ret = [
			'Is guest'=>$this->valueBool($u->isGuest),
			'ID'=>$this->valueBool($u->id),
			'Identity'=>$this->valueModal('Identity', $this->valueVarDump($identity), 'Show identity class'),
		];

		//rbac
		if (Yii::$app->authManager !== null) {
			$am = Yii::$app->authManager;
			$assigns = [];
			$roles = [];
			$perms = [];

			foreach ($am->getAssignments($u->id) as $assign) {
				$assigns[] = $this->valueCode($assign->roleName);
			}
			foreach ($am->roles as $role) {
				$roles[] = Html::tag('span', $role->name, ['class'=>'label label-' . ($u->can($role->name) ? 'success' : 'danger')]);
			}
			foreach ($am->permissions as $perm) {
				$perms[] = Html::tag('span', $perm->name, ['class'=>'label label-' . ($u->can($perm->name) ? 'success' : 'danger')]);
			}

			$ret['Assigned to'] = implode(', ', $assigns);
			$ret['Roles'] = implode(' ', $roles);
			$ret['Permissions'] = implode(' ', $perms);
		}

		return $ret;
	}

	/**
	 * Renders the value with the var dumper
	 *
	 * @param string $value the value to render
	 * @return string the resulting html code
	 */
	protected function valueVarDump($value)
	{
		return VarDumper::dumpAsString($value, 10, true);
	}

	/**
	 * Renders a boolean value
	 *
	 * @param string $value the value to render
	 * @return string the resulting html code
	 */
	protected function valueBool($value)
	{
		if ($value !== false && empty($value)) return Yii::$app->formatter->nullDisplay;
		return Yii::$app->formatter->asBoolean($value);
	}

	/**
	 * Renders the value in a code tag
	 *
	 * @param string $value the value to render
	 * @return string the resulting html code
	 */
	protected function valueCode($value)
	{
		if (empty($value)) return Yii::$app->formatter->nullDisplay;
		return html::tag('code', $value);
	}

	/**
	 * Renders the value in a modal window
	 *
	 * @param string $title the title of the modal
	 * @param string $content the content of the modal
	 * @param string $buttonLabel optional button label
	 * @return string the resulting html code
	 */
	protected function valueModal($title, $content, $buttonLabel=null)
	{
		$randomId = Yii::$app->security->generateRandomString();
		return Button::widget([
			'label'=>$buttonLabel !== null ? $buttonLabel : Yii::t('app', 'Show'),
			'options'=>[
				'class'=>'btn btn-xs btn-primary',
				'onclick'=>new JsExpression("$('#" . $randomId . "').modal()"),
			],
		]) . str_replace(
			['{id}', '{title}', '{content}'],
			[$randomId, $title, $content],
			$this->modalCode);
	}

	/**
	 * Loads and cleans the php info data
	 */
	protected function loadPhpInfo()
	{
		ob_start();
		phpinfo();
		$domDoc = new \DOMDocument();
		$domDoc->loadHTML(ob_get_clean());

		/* @var $node \DOMNode */
		/* @var $childNode \DOMNode */

		foreach ($domDoc->getElementsByTagName('table') as $node) {
			$node->setAttribute('class', 'table table-condensed table-striped table-bordered');
			$node->removeAttribute('width');
			$node->removeAttribute('border');
			$node->removeAttribute('cellpadding');
		}

		foreach ($domDoc->getElementsByTagName('tbody') as $node) {
			$node->setAttribute('style', 'overflow-x: auto;');
		}

		$node = $domDoc->getElementsByTagName('body')->item(0);
		$this->phpInfo = '';
		foreach ($node->childNodes as $childNode) {
			$this->phpInfo .= $node->ownerDocument->saveHTML($childNode);
		}
	}

	protected function flushCache($id)
	{
		if (!isset(Yii::$app->{$id}) || !(Yii::$app->{$id} instanceof Cache)) {
			$msg = Yii::t('Invalid cache to flush: {cache}', ['cache'=>$id]);
			throw new InvalidParamException($msg);
		}

		/* @var $cache \yii\caching\Cache */
		$cache = Yii::$app->{$id};
		if ($cache->flush()) {
			$msg = Yii::t('app', 'Successfully flushed cache `{cache}`', ['cache'=>$id]);
			Yii::$app->session->setFlash('success', $msg);
		} else {
			$msg = Yii::t('app', 'Problem while flushing cache `{cache}`', ['cache'=>$id]);
			Yii::$app->session->setFlash('danger', $msg);
		}
	}

}
