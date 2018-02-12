<?php
/**
 * /app/src/../runtime/giiant/49eb2de82346bc30092f584268252ed2
 *
 * @package default
 */


namespace app\controllers;

use app\components\Controller;
use app\models\Carrier;
use app\models\search\CarrierSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * This is the class for controller "CarrierController".
 */
class CarrierController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Carrier models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CarrierSearch;
        $dataProvider = $searchModel->search($_GET);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * Displays a single Carrier model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        \Yii::$app->session['__crudReturnUrl'] = Url::previous();
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Creates a new Carrier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Carrier;

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
     * Updates an existing Carrier model.
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
     * Finds the Carrier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Carrier the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Carrier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }


}
