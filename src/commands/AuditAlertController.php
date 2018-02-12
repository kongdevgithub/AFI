<?php

namespace app\commands;

use app\components\Helper;
use app\models\User;
use bedezign\yii2\audit\models\AuditError;
use bedezign\yii2\audit\models\AuditJavascript;
use bedezign\yii2\audit\models\AuditMail;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Task runner commands for Audit alert emails.
 */
class AuditAlertController extends Controller
{

    /**
     * @var
     */
    public $email = 'webmaster@afibranding.com.au';


    /**
     * All Alerts
     *
     * @param string|null $email
     * @return int
     */
    public function actionIndex($email = null)
    {
        $this->actionErrorAlert($email);
        $this->actionJavascriptEmail($email);
        $this->actionMailAlert($email);
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Error Alert
     *
     * @param string|null $email
     * @return int
     */
    public function actionErrorAlert($email = null)
    {
        $email = $email ? $email : $this->email;
        if (!$email) {
            $this->stdout("ERROR: could not determine alert email!\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }

        // find all errors to email
        $batch = AuditError::find()->where(['alert_emailed' => 0])->batch();
        foreach ($batch as $auditErrors) {
            /** @var AuditError $model */
            foreach ($auditErrors as $model) {

                // define params and message
                $user = $model->entry->user_id ? User::findOne($model->entry->user_id) : false;
                $url = Url::to(['audit/error/view', 'id' => $model->id], 'https');
                $params = [
                    'entry_id' => $model->entry_id,
                    'message' => $model->message,
                    'file' => $this->cleanPath($model->file),
                    'line' => $model->line,
                    'url' => $url,
                    'link' => Html::a(Yii::t('app', 'view audit error'), $url),
                    'request_url' => Helper::getAuditRequestUrl($model->entry),
                    'user_name' => $user ? $user->label : 'System',
                ];
                $message = [
                    'subject' => Yii::t('app', 'Audit Error in Audit Entry #{entry_id}', $params),
                    'text' => Yii::t('app', implode("\n", [
                        '{message}',
                        'in {file} on line {line}.',
                        '-- by: {user_name}',
                        '-- request: {request_url}',
                        '-- error: {url}',
                    ]), $params),
                    'html' => Yii::t('app', implode('<br>',[
                        '<b>{message}</b>',
                        'in <i>{file}</i> on line <i>{line}</i>.',
                        '-- by: {user_name}',
                        '-- request: {request_url}',
                        '-- error: {link}',
                    ]), $params),
                ];

                // send via email
                Yii::$app->mailer->compose()
                    ->setFrom([$email => 'Audit :: ' . Yii::$app->name])
                    ->setTo($email)
                    ->setSubject($message['subject'])
                    ->setTextBody($message['text'])
                    ->setHtmlBody($message['html'])
                    ->send();

                // send via slack
                $file = $this->cleanPath($model->file) . ':' . $model->line;
                Yii::$app->slack->send('*PHP Error*', ':warning:', [
                    [
                        'color' => '#d9534f',
                        'author_name' => $user ? $user->label : 'System',
                        //'author_icon' => Helper::getUserAvatar($user),
                        'author_link' => $user ? Url::to(['/user/profile/show', 'id' => $user->id], 'https') : null,
                        'text' => $model->message . "\n" . '<' . Helper::getAuditRequestUrl($model->entry) . '>',
                        'footer' => implode(' - ', [
                            '<' . Url::to(['/'], 'https') . '|' . Yii::$app->name . '>',
                            '<' . Url::to(['audit/entry/view', 'id' => $model->entry->id], 'https') . '|Audit #' . $model->entry->id . '>',
                            '<' . Url::to(['audit/error/view', 'id' => $model->id], 'https') . '|Error #' . $model->id . '>',
                            '<http://localhost:8091?message=' . $file . '|' . $file . '>',
                        ]),
                        'footer_icon' => 'https://s3.afi.ink/img/favicon/favicon-16x16.png',
                        'ts' => time(),
                    ],
                ]);

                // mark as alert_emailed
                $model->alert_emailed = 1;
                $model->save(false, ['alert_emailed']);

                $this->stdout("Alert sent for AuditError {$model->id}.\n");
            }
        }
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Javascript Alert
     *
     * @param string|null $email
     * @return int
     */
    public function actionJavascriptEmail($email = null)
    {
        $email = $email ? $email : $this->email;
        if (!$email) {
            $this->stdout("ERROR: could not determine alert email!\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }

        // find all errors to email
        $batch = AuditJavascript::find()->where(['alert_emailed' => 0])->batch();
        foreach ($batch as $auditJavascripts) {
            /** @var AuditJavascript $model */
            foreach ($auditJavascripts as $model) {

                // define params and message
                $user = $model->entry->user_id ? User::findOne($model->entry->user_id) : false;
                $url = Url::to(['audit/javascript/view', 'id' => $model->id], 'https');
                $params = [
                    'entry_id' => $model->entry_id,
                    'message' => $model->message,
                    'url' => $url,
                    'link' => Html::a(Yii::t('app', 'view audit javascript'), $url),
                ];
                $message = [
                    'subject' => Yii::t('app', 'Audit Javascript in Audit Entry #{entry_id}', $params),
                    'text' => Yii::t('app', '{message}' . "\n" . '-- {url}', $params),
                    'html' => Yii::t('app', '<b>{message}</b><br/>-- {link}', $params),
                ];

                // send via email
                Yii::$app->mailer->compose()
                    ->setFrom([$email => 'Audit :: ' . Yii::$app->name])
                    ->setTo($email)
                    ->setSubject($message['subject'])
                    ->setTextBody($message['text'])
                    ->setHtmlBody($message['html'])
                    ->send();

                // send via slack
                Yii::$app->slack->send('*JavaScript Error*', ':warning:', [
                    [
                        'color' => '#d9534f',
                        'author_name' => $user ? $user->label : 'System',
                        //'author_icon' => Helper::getUserAvatar($user),
                        'author_link' => $user ? Url::to(['/user/profile/show', 'id' => $user->id], 'https') : null,
                        'text' => $model->message . "\n" . '<' . Helper::getAuditRequestUrl($model->entry) . '>',
                        'footer' => implode(' - ', [
                            '<' . Url::to(['/'], 'https') . '|' . Yii::$app->name . '>',
                            '<' . Url::to(['audit/entry/view', 'id' => $model->entry->id], 'https') . '|Audit #' . $model->entry->id . '>',
                            '<' . Url::to(['audit/javascript/view', 'id' => $model->id], 'https') . '|JS #' . $model->id . '>',
                        ]),
                        'footer_icon' => 'https://s3.afi.ink/img/favicon/favicon-16x16.png',
                        'ts' => time(),
                    ],
                ]);

                // mark as alert_emailed
                $model->alert_emailed = 1;
                $model->save(false, ['alert_emailed']);

                $this->stdout("Alert sent for AuditJavascript {$model->id}.\n");
            }
        }
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Javascript Alert
     *
     * @param string|null $email
     * @return int
     */
    public function actionMailAlert($email = null)
    {
        $email = $email ? $email : $this->email;
        if (!$email) {
            $this->stdout("ERROR: could not determine alert email!\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }

        // find all errors to email
        $batch = AuditMail::find()->where(['successful' => 0, 'alert_emailed' => 0])->batch();
        foreach ($batch as $auditMails) {
            /** @var AuditMail $model */
            foreach ($auditMails as $model) {

                // define params and message
                $user = $model->entry->user_id ? User::findOne($model->entry->user_id) : false;
                $url = Url::to(['audit/mail/view', 'id' => $model->id], 'https');
                $params = [
                    'entry_id' => $model->entry_id,
                    'url' => $url,
                    'link' => Html::a(Yii::t('app', 'view audit mail'), $url),
                ];
                $message = [
                    'subject' => Yii::t('app', 'Audit Mail Failure in Audit Entry #{entry_id}', $params),
                    'text' => Yii::t('app', 'Audit Mail Failure in Audit Entry #{entry_id}' . "\n" . '-- {url}', $params),
                    'html' => Yii::t('app', '<b>Audit Mail Failure in Audit Entry #{entry_id}</b><br/>-- {link}', $params),
                ];

                // send via email
                Yii::$app->mailer->compose()
                    ->setFrom([$email => 'Audit :: ' . Yii::$app->name])
                    ->setTo($email)
                    ->setSubject($message['subject'])
                    ->setTextBody($message['text'])
                    ->setHtmlBody($message['html'])
                    ->send();

                // send via slack
                Yii::$app->slack->send('*Mail Failure Error*', ':warning:', [
                    [
                        'color' => '#d9534f',
                        'author_name' => $user ? $user->label : 'System',
                        //'author_icon' => Helper::getUserAvatar($user),
                        'author_link' => $user ? Url::to(['/user/profile/show', 'id' => $user->id], 'https') : null,
                        'text' => '<' . Helper::getAuditRequestUrl($model->entry) . '>',
                        'footer' => implode(' - ', [
                            '<' . Url::to(['/'], 'https') . '|' . Yii::$app->name . '>',
                            '<' . Url::to(['audit/entry/view', 'id' => $model->entry->id], 'https') . '|Audit #' . $model->entry->id . '>',
                            '<' . Url::to(['audit/mail/view', 'id' => $model->id], 'https') . '|Mail #' . $model->id . '>',
                        ]),
                        'footer_icon' => 'https://s3.afi.ink/img/favicon/favicon-16x16.png',
                        'ts' => time(),
                    ],
                ]);

                // mark as alert_emailed
                $model->alert_emailed = 1;
                $model->save(false, ['alert_emailed']);

                $this->stdout("Alert sent for AuditMail {$model->id}.\n");
            }
        }
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param $path
     * @return string
     */
    private function cleanPath($path)
    {
        $path = realpath($path);
        $root = realpath(Yii::getAlias('@root'));
        return strpos($path, $root) === 0 ? substr($path, strlen($root)) : $path;
    }

}
