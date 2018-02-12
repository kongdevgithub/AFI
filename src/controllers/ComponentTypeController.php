<?php

namespace app\controllers;

use app\components\Controller;
use app\models\ComponentType;
use app\models\search\ComponentTypeSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * This is the class for controller "app\controllers\ComponentTypeController".
 */
class ComponentTypeController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all ComponentType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ComponentTypeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        Tabs::clearLocalStorage();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single ComponentType model.
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
     * Creates a new ComponentType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ComponentType;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component Type has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing ComponentType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component Type has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing ComponentType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component Type has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the ComponentType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ComponentType the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ComponentType::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
