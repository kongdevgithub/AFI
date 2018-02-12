<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "feedback".
 *
 * @property \app\models\Job[] $jobs
 *
 * @mixin LinkBehavior
 */
class Feedback extends base\Feedback
{
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['score'] = ['score', 'comments'];
        $scenarios['dismiss'] = ['staff_comments', 'followup_at'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors[] = TimestampBehavior::className();
        //$behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->scenario == 'score') {
            $this->submitted_at = time();
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(Job::className(), ['id' => 'job_id'])
            ->viaTable(FeedbackToJob::tableName(), ['feedback_id' => 'id'])
            ->andWhere('job.deleted_at IS NULL');
        //->inverseOf('contacts');
        //->via('postToTag');
    }

    /**
     * @return array
     */
    public static function getFeedbackContacts()
    {
        $jobs = Job::find()
            ->joinWith(['contact'])
            ->notDeleted()
            ->andWhere(['or', ['contact.feedback_sent_at' => null], ['<=', 'contact.feedback_sent_at', strtotime('-90days')]])
            ->andWhere(['contact.feedback_unsubscribed_at' => null])
            ->andWhere(['job.feedback_at' => null])
            ->andWhere(['>', 'job.quote_total_price', 500])
            ->andWhere(['<', 'job.complete_at', strtotime('-10 days')]);
        $feedbackContacts = [];
        foreach ($jobs->all() as $k => $job) {
            $feedbackContacts[$job->contact_id][] = $job->id;
        }
        return $feedbackContacts;
    }
}
