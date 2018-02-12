<?php

namespace app\controllers;

use app\components\Controller;
use app\components\EmailManager;
use app\components\ReturnUrl;
use app\models\Contact;
use app\models\Feedback;
use app\models\FeedbackToJob;
use app\traits\TwoFactorTrait;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;

/**
 * This is the class for controller "FeedbackController".
 */
class FeedbackController extends Controller
{
    use TwoFactorTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $controller = $this;
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) use ($controller) {
                            if (in_array($action->id, ['thank-you', 'ajax-score', 'unsubscribe'])) {
                                return true;
                            }
                            $permission = str_replace('/', '_', 'app_' . $controller->id . '_' . $action->id);
                            return Yii::$app->user->can($permission, ['route' => true]);
                        },
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (in_array($action->id, ['thank-you', 'ajax-score', 'unsubscribe'])) {
            return true;
        }
        if ($tfa = $this->beforeActionTwoFactor($action)) {
            return $tfa;
        }
        return true;
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionDismiss($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'dismiss';
        $post = Yii::$app->request->post();

        if ($post) {
            if ($model->load($post)) {
                $model->followup_at = time();
                if ($model->save()) {
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Feedback has been dismissed.'));
                    return $this->redirect(ReturnUrl::getUrl(['site/index']));
                }
            }
        } else {
            $model->load(Yii::$app->request->get());
        }
        return $this->render('dismiss', compact('model'));
    }

    /**
     * @param integer $id
     * @param $score
     * @param $key
     * @param null $complete
     * @return mixed
     */
    public function actionThankYou($id, $score, $key, $complete = null)
    {
        $this->layout = '@app/views/layouts/narrow';
        $model = $this->findFeedbackModel($id, $key);
        $post = Yii::$app->request->post();
        $model->scenario = 'score';


        if ($model->submitted_at && $model->submitted_at < strtotime('-1 hour')) {
            $complete = 1;
            Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'This feedback form has already been submitted.'));
        } elseif ($post) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Your additional feedback has been sent to our service team.'));
                return $this->redirect(ReturnUrl::getUrl(['thank-you', 'id' => $model->id, 'score' => $model->score, 'key' => $key, 'complete' => 1]));
            }
        } else {
            $model->score = $score;
            $model->save();
            $model->comments = ''; // make the form blank
        }

        return $this->render('thank-you', ['model' => $model, 'key' => $key, 'complete' => $complete]);
    }

    /**
     * @param integer $id
     * @param $score
     * @param $key
     * @return mixed
     */
    public function actionAjaxScore($id, $score, $key)
    {
        $model = $this->findFeedbackModel($id, $key);
        $model->scenario = 'score';
        if ($model->submitted_at && $model->submitted_at < strtotime('-1 hour')) {
            return '';
        }
        $model->score = $score;
        $model->save();
        return 'ok';
    }

    /**
     * @param integer $id
     * @param $key
     * @return mixed
     */
    public function actionUnsubscribe($id, $key)
    {
        $this->layout = '@app/views/layouts/narrow';
        $model = $this->findContactModel($id, $key);
        $model->feedback_unsubscribed_at = time();
        $model->save();
        EmailManager::sendFeedbackUnsubscribeAlert($model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'You have been unsubscribed from feedback emails.'));
        return $this->render('unsubscribe', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param integer $id
     * @param $key
     * @return mixed
     */
    public function actionPunish($id, $key)
    {
        $this->layout = '@app/views/layouts/narrow';
        $model = $this->findContactModel($id, $key);
        return $this->render('punish', ['model' => $model, 'key' => $key]);
    }


    /**
     * @return \yii\web\Response
     */
    public function actionTest()
    {
        $contact_id = 2;
        $job_id = 39249;
        $feedback = new Feedback();
        $feedback->contact_id = $contact_id;
        $feedback->save();
        $feedbackToJob = new FeedbackToJob();
        $feedbackToJob->feedback_id = $feedback->id;
        $feedbackToJob->job_id = $job_id;
        $feedbackToJob->save();
        EmailManager::sendFeedbackSurvey($feedback, [Yii::$app->user->identity->email => Yii::$app->user->identity->label]);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Test NPS email has been sent to {email}.', [
            'email' => Yii::$app->user->identity->email,
        ]));
        return $this->redirect(ReturnUrl::getUrl(['site/index']));
    }

    /**
     * Finds the Feedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Feedback the loaded model
     */
    protected function findFeedbackModel($id, $key)
    {
        $this->validateKey($id, $key);
        if (($model = Feedback::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }

    /**
     * Finds the Feedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Contact the loaded model
     */
    protected function findContactModel($id, $key)
    {
        $this->validateKey($id, $key);
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @param $key
     * @throws HttpException
     */
    private function validateKey($id, $key)
    {
        if ($key != md5($id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))) {
            throw new HttpException(405, 'You do not have permission to view the requested page.');
        }
    }


    /**
     * Finds the Carrier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws HttpException if the model cannot be found
     * @param integer $id
     * @return Feedback the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Feedback::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }
}
