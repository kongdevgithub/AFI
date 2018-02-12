<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\models\Notification;
use app\models\Product;
use app\models\search\NotificationSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use dmstr\bootstrap\Tabs;

/**
 * This is the class for controller "NotificationController".
 */
class NotificationController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Notification models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NotificationSearch;
        $dataProvider = $searchModel->search($_GET);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * Displays a single Notification model.
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
     * Creates a new Notification model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Notification;

        try {
            if ($model->load($_POST) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } elseif (!\Yii::$app->request->isPost) {
                $model->load($_GET);
            }
        } catch (\Exception $e) {
            $msg = (isset($e->errorInfo[2])) ? $e->errorInfo[2] : $e->getMessage();
            $model->addError('_exception', $msg);
        }
        return $this->render('create', ['model' => $model]);
    }


    /**
     * Updates an existing Notification model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(Url::previous());
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Notification model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Log::log('deleted notification', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Notification has been deleted.'));

        if ($model->model_name == Job::className()) {
            $ru = ['job/view', 'id' => $model->model_id];
        } elseif ($model->model_name == Product::className()) {
            $ru = ['product/view', 'id' => $model->model_id];
        } elseif ($model->model_name == Item::className()) {
            $ru = ['item/view', 'id' => $model->model_id];
        } else {
            $ru = ['index'];
        }

        return $this->redirect(ReturnUrl::getUrl($ru));
    }

    /**
     * Finds the Notification model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Notification the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Notification::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }


}
