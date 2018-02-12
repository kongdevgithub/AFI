<?php

namespace app\controllers;

use app\models\CompanyRate;
use app\models\form\CompanyRateForm;
use app\models\form\CompanyRateImportForm;
use app\models\search\CompanyRateSearch;
use yii\web\Controller;
use Yii;
use yii\web\HttpException;
use cornernote\returnurl\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;

/**
 * This is the class for controller "app\controllers\CompanyRateController".
 */
class CompanyRateController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;


    /**
     * Lists all CompanyRate models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyRateSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * Displays a single CompanyRate model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }


    /**
     * Creates a new CompanyRate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CompanyRateForm;
        $model->companyRate = new CompanyRate();
        $model->companyRate->loadDefaultValues();
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company Rate has been created.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->companyRate->id]));
            }
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing CompanyRate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new CompanyRateForm;
        $model->companyRate = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company Rate has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->companyRate->id]));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CompanyRate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $model->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company Rate has been deleted.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the CompanyRate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return CompanyRate the loaded model
     */
    protected function findModel($id)
    {
        if (($model = CompanyRate::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
