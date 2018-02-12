<?php

namespace app\modules\client\controllers;

use app\components\ReturnUrl;
use app\models\Address;
use app\models\Company;
use app\models\search\AddressSearch;
use app\traits\AccessBehaviorTrait;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "app\controllers\AddressController".
 */
class AddressController extends Controller
{

    public $layout = '@app/views/layouts/main';

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
