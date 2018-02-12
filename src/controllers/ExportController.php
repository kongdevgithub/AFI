<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Export;
use app\models\search\ExportSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use yii\web\HttpException;

/**
 * This is the class for controller "ExportController".
 */
class ExportController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Export models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExportSearch;
        $dataProvider = $searchModel->search($_GET);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * Displays a single Export model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if ($model->status != 'complete') {
            $model->spoolGearman();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Export model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Export the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Export::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }

}
