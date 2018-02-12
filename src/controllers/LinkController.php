<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Log;
use app\models\Link;
use app\components\ReturnUrl;
use app\models\search\LinkSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\LinkController".
 */
class LinkController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    public function actionCreate()
    {
        $model = new Link;
        //$model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('created link', $model->model_name, $model->model_id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Link has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        //$model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('updated link', $model->model_name, $model->model_id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Link has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    public function actionSort()
    {
        if (Yii::$app->request->post('Link')) {
            foreach (Yii::$app->request->post('Link') as $k => $id) {
                $link = Link::findOne($id);
                $link->sort_order = $k;
                $link->save(false);
            }
        }
    }

    /**
     * Lists all Link models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LinkSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Link model.
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
     * Deletes an existing Link model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Link has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Link model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Link the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Link::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
