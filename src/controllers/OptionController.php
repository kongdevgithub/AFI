<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Option;
use app\models\search\OptionSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * This is the class for controller "app\controllers\OptionController".
 */
class OptionController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Option models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OptionSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        Tabs::clearLocalStorage();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Option model.
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
     * Creates a new Option model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Option;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Option has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Option model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Option has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing Option model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Option has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Option the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Option::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
