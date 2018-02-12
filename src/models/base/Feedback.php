<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "feedback".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $score
 * @property string $comments
 * @property string $staff_comments
 * @property integer $followup_at
 * @property integer $submitted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Contact $contact
 * @property \app\models\FeedbackToJob[] $feedbackToJobs
 */
class Feedback extends ActiveRecord
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
        return 'feedback';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'contact_id', 'score', 'comments', 'submitted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'contact_id', 'score', 'comments', 'submitted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'contact_id', 'score', 'comments', 'submitted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id'], 'required'],
            [['contact_id', 'score', 'followup_at', 'submitted_at'], 'integer'],
            [['comments', 'staff_comments'], 'string'],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Contact::className(), 'targetAttribute' => ['contact_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'contact_id' => Yii::t('models', 'Contact ID'),
            'score' => Yii::t('models', 'Score'),
            'comments' => Yii::t('models', 'Comments'),
            'staff_comments' => Yii::t('models', 'Staff Comments'),
            'followup_at' => Yii::t('models', 'Followup At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'submitted_at' => Yii::t('models', 'Submitted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(\app\models\Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbackToJobs()
    {
        return $this->hasMany(\app\models\FeedbackToJob::className(), ['feedback_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\FeedbackQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\FeedbackQuery(get_called_class());
    }

}
