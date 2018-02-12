<?php

namespace app\controllers;

use app\components\Controller;
use app\models\ProductToOption;
use app\models\search\ProductToOptionSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * This is the class for controller "app\controllers\ProductToOptionController".
 */
class ProductToOptionController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all ProductToOption models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductToOptionSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        Tabs::clearLocalStorage();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single ProductToOption model.
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        Tabs::rememberActiveState();
        $model = $this->findModel($id);

        return $this->render('view', compact('model'));
    }

    /**
     * Creates a new ProductToOption model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductToOption;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product To Option has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing ProductToOption model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product To Option has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing ProductToOption model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product To Option has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the ProductToOption model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductToOption the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductToOption::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
