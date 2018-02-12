<?php

namespace app\controllers;

use app\components\Controller;
use app\components\GearmanManager;
use app\components\ReturnUrl;
use Yii;
use yii\helpers\Json;

/**
 * This is the class for controller "HubSpotController".
 */
class HubSpotController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     *
     */
    public function actionWebhook()
    {
        GearmanManager::runHubSpotWebhook(Json::decode(Yii::$app->getRequest()->getRawBody()), time());
    }

    /**
     * @param null $code
     * @param null $force
     * @return \yii\web\Response
     */
    public function actionOauth($code = null, $force = null)
    {
        $hubSpotApi = Yii::$app->hubSpotApi;

        if ($force) {
            $hubSpotApi->redirect['force'] = $force;
        }

        $hubSpotApi->redirect['ru'] = ReturnUrl::getRequestToken();

        if ((!$force && $hubSpotApi->getToken()) || ($code && $hubSpotApi->oauth($code))) {
            return $this->redirect(ReturnUrl::getUrl());
        }

        return $this->redirect($hubSpotApi->getOauthUrl());
    }
}
