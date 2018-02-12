<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\search\PackageTypeSearch;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\Response;
use app\models\PackageType;
use app\traits\AccessBehaviorTrait;
use yii\web\HttpException;
use yii\helpers\Url;
use dmstr\bootstrap\Tabs;


/**
 * This is the class for controller "PackageTypeController".
 */
class PackageTypeController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all PackageType models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PackageTypeSearch;
        $dataProvider = $searchModel->search($_GET);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * Displays a single PackageType model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Creates a new PackageType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PackageType;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package Type has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }


    /**
     * Updates an existing PackageType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package Type has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing PackageType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package Type has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }


    /**
     * Finds the PackageType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return PackageType the loaded model
     */
    protected function findModel($id)
    {
        if (($model = PackageType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new PackageTypeSearch;

        $params = $_GET;

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;
        return $dataProvider->getModels();
    }

}
