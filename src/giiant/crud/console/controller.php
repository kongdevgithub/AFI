<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass.'Search';
}

$pks = $generator->getTableSchema()->primaryKey;
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>\base;

use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->searchModelClass, '\\') ?><?php if (isset($searchModelAlias)):?> as <?= $searchModelAlias ?><?php endif ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use Yii;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use dmstr\bootstrap\Tabs;
use cornernote\returnurl\ReturnUrl;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    /**
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     *
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new <?= $modelClass ?>;
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' has been created.') ?>);
            return $this->redirect(ReturnUrl::getUrl(['view', <?= $urlParams ?>]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' has been updated.') ?>);
            return $this->redirect(ReturnUrl::getUrl(['view', <?= $urlParams ?>]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Copies an existing <?= $modelClass ?> model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionCopy(<?= $actionParams ?>)
    {
        $modelCopy = $this->findModel(<?= $actionParams ?>);
        $model = new <?= $modelClass ?>;
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' has been updated.') ?>);
            return $this->redirect(ReturnUrl::getUrl(['view', <?= $urlParams ?>]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(['<?= $modelClass ?>' => $modelCopy->attributes]);
        }

        return $this->render('copy', [
            'model' => $model,
            'modelCopy' => $modelCopy,
        ]);
    }
    
    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);
       
        $model->delete();
        Yii::$app->getSession()->addFlash('success', <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass)) . ' has been deleted.') ?>);
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     * @return <?= $modelClass ?> the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$'.$pks[0];
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
