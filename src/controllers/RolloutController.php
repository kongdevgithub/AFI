<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\Log;
use app\models\Rollout;
use app\models\search\RolloutSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "app\controllers\RolloutController".
 */
class RolloutController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Rollout models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RolloutSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Rollout model.
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', compact('model'));
    }

    /**
     * Creates a new Rollout model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Rollout;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Rollout has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Rollout model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Rollout has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing Rollout model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Rollout has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }


    /**
     * @inheritdoc
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'status';

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction->commit();
            Log::log('updated status', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Rollout has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new RolloutSearch;
        $dataProvider = $searchModel->search($_GET);
        $dataProvider->pagination->pageSize = 200;

        /** @var Rollout[] $models */
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->name];
        }
        return ['results' => $results];
    }

    /**
     * Finds the Rollout model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Rollout the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Rollout::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
