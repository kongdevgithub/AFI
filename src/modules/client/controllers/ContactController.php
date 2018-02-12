<?php

namespace app\modules\client\controllers;

use app\components\GearmanManager;
use app\models\Company;
use app\models\Contact;
use app\models\ContactToCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use app\models\Log;
use app\models\search\ContactSearch;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "ContactController".
 */
class ContactController extends Controller
{
    public $layout = '@app/views/layouts/main';

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new ContactSearch;
        $params = $_GET;
        if (isset($_GET['q'])) {
            $params['ContactSearch']['keywords'] = $_GET['q'];
        }

        // client permissions
        if (in_array('client', Yii::$app->user->identity->getRoles())) {
            $params['ContactSearch']['company_id'] = Yii::$app->user->identity->getClientCompanies();
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;

        /** @var Contact[] $models */
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->getLabelWithEmail()];
        }

        $selected = false;
        if (isset($_GET['ContactSearch']['company_id'])) {
            $company = Company::findOne($_GET['ContactSearch']['company_id']);
            if ($company) {
                $selected = $company->default_contact_id;
            }
        }

        return [
            'results' => $results,
            'selected' => $selected,
        ];
    }

    /**
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
