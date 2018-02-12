<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "contact_to_company".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $company_id
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Contact $contact
 * @property \app\models\Company $company
 */
class ContactToCompany extends ActiveRecord
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
        return 'contact_to_company';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'contact_id', 'company_id', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'contact_id', 'company_id', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'contact_id', 'company_id', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'company_id'], 'required'],
            [['contact_id', 'company_id', 'deleted_at'], 'integer'],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Contact::className(), 'targetAttribute' => ['contact_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']]
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
            'company_id' => Yii::t('models', 'Company ID'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
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
    public function getCompany()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'company_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ContactToCompanyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ContactToCompanyQuery(get_called_class());
    }

}
