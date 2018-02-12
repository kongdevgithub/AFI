<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\EmailManager;
use app\components\Helper;
use app\models\Contact;
use app\models\Feedback;
use app\models\FeedbackToJob;
use app\models\Job;
use app\models\query\JobQuery;
use Yii;
use yii\console\Controller;

/**
 * Class FeedbackController
 * @package app\commands
 */
class FeedbackController extends Controller
{

    /**
     * @param int $limit
     * @return int
     */
    public function actionSend($limit = 20)
    {
        $this->stdout('BUILDING FEEDBACK LISTS' . "\n");
        $feedbackContacts = Feedback::getFeedbackContacts();
        if (!$feedbackContacts) {
            return self::EXIT_CODE_ERROR;
        }

        $transaction = Yii::$app->dbData->beginTransaction();
        $i = 0;
        $count = count($feedbackContacts);
        $this->stdout('SENDING FEEDBACK SURVEYS' . "\n");
        foreach ($feedbackContacts as $contact_id => $jobs) {
            $i++;
            if ($i > $limit) break;
            $this->stdout(CommandStats::stats($i, $count));
            $contact = Contact::findOne($contact_id);
            $this->stdout($contact->label . ' <' . $contact->email . '>' . "\n");
            $contact->feedback_sent_at = time();
            if (!$contact->save()) {
                $this->stdout('ERROR: ' . Helper::getErrorString($contact) . "\n");
                $transaction->rollBack();
                return self::EXIT_CODE_ERROR;
            }
            $feedback = new Feedback();
            $feedback->contact_id = $contact->id;
            if (!$feedback->save()) {
                $this->stdout('ERROR: ' . Helper::getErrorString($feedback) . "\n");
                $transaction->rollBack();
                return self::EXIT_CODE_ERROR;
            }
            foreach ($jobs as $job_id) {
                $feedbackToJob = new FeedbackToJob();
                $feedbackToJob->feedback_id = $feedback->id;
                $feedbackToJob->job_id = $job_id;
                if (!$feedbackToJob->save()) {
                    $this->stdout('ERROR: ' . Helper::getErrorString($feedbackToJob) . "\n");
                    $transaction->rollBack();
                    return self::EXIT_CODE_ERROR;
                }
                $job = Job::findOne($job_id);
                $job->feedback_at = time();
                if (!$job->save(false)) {
                    $this->stdout('ERROR: ' . Helper::getErrorString($job) . "\n");
                    return self::EXIT_CODE_ERROR;
                }
            }

            EmailManager::sendFeedbackSurvey($feedback);
        }

        $transaction->commit();
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }


}
