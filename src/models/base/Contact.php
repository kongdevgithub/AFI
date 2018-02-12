<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "contact".
 *
 * @property integer $id
 * @property integer $default_company_id
 * @property string $first_name
 * @property string $last_name
 * @property string $status
 * @property string $phone
 * @property string $fax
 * @property string $email
 * @property integer $deleted_at
 * @property integer $merge_id
 * @property integer $feedback_sent_at
 * @property integer $feedback_unsubscribed_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Company[] $companies
 * @property \app\models\Company $defaultCompany
 * @property \app\models\ContactToCompany[] $contactToCompanies
 * @property \app\models\Feedback[] $feedbacks
 * @property \app\models\Job[] $jobs
 */
class Contact extends ActiveRecord
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
        return 'contact';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'default_company_id', 'first_name', 'last_name', 'status', 'phone', 'fax', 'email', 'deleted_at', 'merge_id', 'feedback_sent_at', 'feedback_unsubscribed_at', 'created_at', 'updated_at'],
            'create' => ['id', 'default_company_id', 'first_name', 'last_name', 'status', 'phone', 'fax', 'email', 'deleted_at', 'merge_id', 'feedback_sent_at', 'feedback_unsubscribed_at', 'created_at', 'updated_at'],
            'update' => ['id', 'default_company_id', 'first_name', 'last_name', 'status', 'phone', 'fax', 'email', 'deleted_at', 'merge_id', 'feedback_sent_at', 'feedback_unsubscribed_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['default_company_id', 'deleted_at', 'merge_id', 'feedback_sent_at', 'feedback_unsubscribed_at'], 'integer'],
            [['first_name'], 'required'],
            [['first_name', 'last_name', 'status', 'phone', 'fax', 'email'], 'string', 'max' => 255],
            [['default_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['default_company_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'default_company_id' => Yii::t('models', 'Default Company ID'),
            'first_name' => Yii::t('models', 'First Name'),
            'last_name' => Yii::t('models', 'Last Name'),
            'status' => Yii::t('models', 'Status'),
            'phone' => Yii::t('models', 'Phone'),
            'fax' => Yii::t('models', 'Fax'),
            'email' => Yii::t('models', 'Email'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'merge_id' => Yii::t('models', 'Merge ID'),
            'feedback_sent_at' => Yii::t('models', 'Feedback Sent At'),
            'feedback_unsubscribed_at' => Yii::t('models', 'Feedback Unsubscribed At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(\app\models\Company::className(), ['default_contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCompany()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'default_company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactToCompanies()
    {
        return $this->hasMany(\app\models\ContactToCompany::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbacks()
    {
        return $this->hasMany(\app\models\Feedback::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(\app\models\Job::className(), ['contact_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ContactQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ContactQuery(get_called_class());
    }

}
