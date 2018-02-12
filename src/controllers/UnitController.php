<?php

namespace app\controllers;

use app\components\Controller;
use app\models\form\UnitProgressForm;
use app\models\form\UnitStatusForm;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\components\ReturnUrl;
use app\models\search\UnitSearch;
use app\models\Unit;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\UnitController".
 */
class UnitController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Unit models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UnitSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Unit model.
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
     * Creates a new Unit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Unit;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Unit has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Unit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Unit has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     * Deletes an existing Unit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Unit has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Unit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Unit the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Unit::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionStatus($id)
    {
        $model = new UnitStatusForm();
        $model->unit = $this->findModel($id);
        $model->unit->scenario = 'status';
        $model->unit->loadDefaultValues();
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Log::log('updated status', $model->unit->item->className(), $model->unit->item_id);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Unit has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/item/view', 'id' => $model->unit->item->id]));
            }
        } else {
            $model->old_status = $model->unit->status;
            $model->new_status = $model->unit->getNextStatus();
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @param $status
     * @return string|\yii\web\Response
     */
    public function actionProgress($status = null)
    {
        $model = new UnitProgressForm();
        $model->job_id = isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : false;
        $model->item_ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : false;
        $model->old_status = $model->getStatus($status);

        $post = Yii::$app->request->post();
        if (!empty($post['UnitProgressForm'])) {
            $model->setAttributes($post);
            if ($model->save()) {
                if ($model->job_id) {
                    Log::log('progress status', Job::className(), $model->job_id);
                }
                if ($model->item_ids) {
                    foreach ($model->item_ids as $item_id) {
                        Log::log('progress status', Item::className(), $item_id);
                    }
                }
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Units have been updated.'));
                return $this->redirect(ReturnUrl::getUrl($model->job_id ? ['/job/view', 'id' => $model->job_id] : Url::home()));
            }
        } else {
            $unit = new Unit;
            $unit->sendToStatus(null);
            $unit->enterWorkflow(explode('/', $model->old_status)[0]);
            $unit->status = $model->old_status;
            $unit->initStatus();
            $model->new_status = $unit->getNextStatus();
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('progress', ['model' => $model]);
    }

}
