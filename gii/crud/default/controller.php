<?php
use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View $this */
/* @var $generator \asinfotrack\yii2\toolbox\gii\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass . 'Search';
}

/** @var ActiveRecordInterface $class */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;

/* @var $model <?= $modelClass; ?> */ 
/* @var $searchModel <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass; ?> */ 

class <?= $controllerClass ?> extends <?= '\\' . ltrim($generator->baseControllerClass, '\\') . "\n" ?>
{

	public function behaviors()
	{
		return [
			'access'=>[
				'class'=>AccessControl::className(),
				'rules'=>[
					[
						'actions'=>['index', 'create', 'view', 'update', 'delete'],
						'allow'=>true,
						'roles'=>['@'],
					],
				],
			],
			'verbs'=>[
				'class'=>VerbFilter::className(),
				'actions'=>[
					'delete'=>['post'],
				],
			],
		];
	}

	/**
	 * Lists all <?= $modelClass ?> models
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

		return $this->render('index', [
			'dataProvider'=>$dataProvider,
			'searchModel'=>$searchModel,
		]);
	}

	/**
	 * Displays a single <?= $modelClass ?> model
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionView(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);
		
		return $this->render('view', [
			'model'=>$model,
		]);
	}

	public function actionCreate()
	{
		$model = new <?= $modelClass ?>;
        $loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->save()) {
			return $this->redirect(['view', <?= str_replace(' => ', '=>', $urlParams) ?>]);
		} else {
			return $this->render('create', [
				'model'=>$model,
			]);
		}
	}

	public function actionUpdate(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);
        $loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->save()) {
			return $this->redirect(['view', <?= str_replace(' => ', '=>', $urlParams); ?>]);
		} else {
			return $this->render('update', [
				'model'=>$model,
			]);
		}
	}

	public function actionDelete(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);
		$model->delete();

		return $this->redirect(['index']);
	}

	/**
	 * Find a model by its primary key
     *
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
	 * @return <?=				   $modelClass ?> the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel(<?= $actionParams ?>)
	{
<?php
if (count($pks) === 1) {
	$condition = '$id';
} else {
	$condition = [];
	foreach ($pks as $pk) {
		$condition[] = "'$pk' => \$$pk";
	}
	$condition = '[' . implode(', ', $condition) . ']';
}
?>
		$model = <?= $modelClass ?>::findOne(<?= $condition ?>);
		if ($model !== null) return $model;
		throw new NotFoundHttpException('The requested page does not exist.');
	}
	
}
