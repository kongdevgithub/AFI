<?php

namespace app\modules\client\controllers;

use app\models\Company;
use app\models\search\CompanySearch;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "CompanyController".
 */
class CompanyController extends Controller
{
    public $layout = '@app/views/layouts/main';

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new CompanySearch;
        $params = $_GET;
        if (isset($_GET['q'])) {
            $params['CompanySearch']['keywords'] = $_GET['q'];
        }

        // client permissions
        if (in_array('client', Yii::$app->user->identity->getRoles())) {
            $params['CompanySearch']['id'] = Yii::$app->user->identity->getClientCompanies();
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->name];
        }
        return ['results' => $results];
    }

    /**
     * @param $id
     * @return array|Company
     * @throws \yii\web\HttpException
     */
    public function actionJsonView($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // client permissions
        if (in_array('client', Yii::$app->user->identity->getRoles())) {
            if (!in_array($id, Yii::$app->user->identity->getClientCompanies())) {
                $id = false;
            }
        }

        return $this->findModel($id);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
