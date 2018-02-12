<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "feedback_to_job".
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property integer $job_id
 *
 * @property \app\models\Job $job
 * @property \app\models\Feedback $feedback
 */
class FeedbackToJob extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbData;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feedback_to_job';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'feedback_id', 'job_id'],
            'create' => ['id', 'feedback_id', 'job_id'],
            'update' => ['id', 'feedback_id', 'job_id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feedback_id', 'job_id'], 'required'],
            [['feedback_id', 'job_id'], 'integer'],
            [['job_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Job::className(), 'targetAttribute' => ['job_id' => 'id']],
            [['feedback_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Feedback::className(), 'targetAttribute' => ['feedback_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'feedback_id' => Yii::t('models', 'Feedback ID'),
            'job_id' => Yii::t('models', 'Job ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(\app\models\Job::className(), ['id' => 'job_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedback()
    {
        return $this->hasOne(\app\models\Feedback::className(), ['id' => 'feedback_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\FeedbackToJobQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\FeedbackToJobQuery(get_called_class());
    }

}
