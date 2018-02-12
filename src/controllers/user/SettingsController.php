<?php

namespace app\controllers\user;

use app\models\Profile;
use app\models\User;
use app\traits\TwoFactorTrait;
use dektrium\user\controllers\SettingsController as BaseSettingsController;
use dektrium\user\models\SettingsForm;
use Yii;

/**
 * Class SettingsController
 * @package app\controllers\user
 */
class SettingsController extends BaseSettingsController
{

    use TwoFactorTrait;

    /**
     * Event is triggered before updating user's application settings.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_BEFORE_APPLICATION_UPDATE = 'beforeApplicationUpdate';

    /**
     * Event is triggered after updating user's application settings.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_AFTER_APPLICATION_UPDATE = 'afterApplicationUpdate';


    public $layout = '@app/views/layouts/box';

    /** @inheritdoc */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'][0]['actions'][] = 'application';
        return $behaviors;
    }


    /**
     * Displays page where user can update application settings.
     *
     * @return string|\yii\web\Response
     */
    public function actionApplication()
    {
        $model = User::findOne(Yii::$app->user->identity->id);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('user', 'Your application settings have been updated'));
            return $this->refresh();
        }

        return $this->render('application', [
            'model' => $model,
        ]);
    }
}