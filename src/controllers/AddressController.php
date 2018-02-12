<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\Address;
use app\models\Company;
use app\models\search\AddressSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "app\controllers\AddressController".
 */
class AddressController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Address models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AddressSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Address model.
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
     * Creates a new Address model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Address;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Address has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Address model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Address has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing Address model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Address has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new AddressSearch;

        $params = $_GET;

        // client permissions
        if (in_array('client', Yii::$app->user->identity->getRoles())) {
            $params['AddressSearch']['model_id'] = Yii::$app->user->identity->getClientCompanies();
            $params['AddressSearch']['model_name'] = Company::className();
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;
        return $dataProvider->getModels();
    }


    /**
     * Finds the Address model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Address the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Address::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
