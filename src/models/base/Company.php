<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "company".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $fax
 * @property string $website
 * @property string $status
 * @property integer $staff_rep_id
 * @property integer $price_structure_id
 * @property integer $account_term_id
 * @property integer $industry_id
 * @property integer $job_type_id
 * @property integer $default_contact_id
 * @property integer $deleted_at
 * @property string $rates_encoded
 * @property integer $merge_id
 * @property integer $purchase_order_required
 * @property string $delivery_docket_header
 * @property string $first_job_due_date
 * @property string $last_job_due_date
 * @property integer $excludes_tax
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\AccountTerm $accountTerm
 * @property \app\models\Contact $defaultContact
 * @property \app\models\Industry $industry
 * @property \app\models\JobType $jobType
 * @property \app\models\PriceStructure $priceStructure
 * @property \app\models\CompanyRate[] $companyRates
 * @property \app\models\Contact[] $contacts
 * @property \app\models\ContactToCompany[] $contactToCompanies
 * @property \app\models\Item[] $items
 * @property \app\models\Job[] $jobs
 * @property \app\models\Rollout[] $rollouts
 */
class Company extends ActiveRecord
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
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price_structure_id', 'account_term_id', 'job_type_id'], 'required'],
            [['staff_rep_id', 'price_structure_id', 'account_term_id', 'industry_id', 'job_type_id', 'default_contact_id', 'deleted_at', 'merge_id', 'purchase_order_required', 'excludes_tax'], 'integer'],
            [['rates_encoded', 'delivery_docket_header'], 'string'],
            [['first_job_due_date', 'last_job_due_date'], 'safe'],
            [['name', 'phone', 'fax', 'website', 'status'], 'string', 'max' => 255],
            [['account_term_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\AccountTerm::className(), 'targetAttribute' => ['account_term_id' => 'id']],
            [['default_contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Contact::className(), 'targetAttribute' => ['default_contact_id' => 'id']],
            [['industry_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Industry::className(), 'targetAttribute' => ['industry_id' => 'id']],
            [['job_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\JobType::className(), 'targetAttribute' => ['job_type_id' => 'id']],
            [['price_structure_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\PriceStructure::className(), 'targetAttribute' => ['price_structure_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'name' => Yii::t('models', 'Name'),
            'phone' => Yii::t('models', 'Phone'),
            'fax' => Yii::t('models', 'Fax'),
            'website' => Yii::t('models', 'Website'),
            'status' => Yii::t('models', 'Status'),
            'staff_rep_id' => Yii::t('models', 'Staff Rep ID'),
            'price_structure_id' => Yii::t('models', 'Price Structure ID'),
            'account_term_id' => Yii::t('models', 'Account Term ID'),
            'industry_id' => Yii::t('models', 'Industry ID'),
            'job_type_id' => Yii::t('models', 'Job Type ID'),
            'default_contact_id' => Yii::t('models', 'Default Contact ID'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'rates_encoded' => Yii::t('models', 'Rates Encoded'),
            'merge_id' => Yii::t('models', 'Merge ID'),
            'purchase_order_required' => Yii::t('models', 'Purchase Order Required'),
            'delivery_docket_header' => Yii::t('models', 'Delivery Docket Header'),
            'first_job_due_date' => Yii::t('models', 'First Job Due Date'),
            'last_job_due_date' => Yii::t('models', 'Last Job Due Date'),
            'excludes_tax' => Yii::t('models', 'Excludes Tax'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountTerm()
    {
        return $this->hasOne(\app\models\AccountTerm::className(), ['id' => 'account_term_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultContact()
    {
        return $this->hasOne(\app\models\Contact::className(), ['id' => 'default_contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIndustry()
    {
        return $this->hasOne(\app\models\Industry::className(), ['id' => 'industry_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobType()
    {
        return $this->hasOne(\app\models\JobType::className(), ['id' => 'job_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPriceStructure()
    {
        return $this->hasOne(\app\models\PriceStructure::className(), ['id' => 'price_structure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRates()
    {
        return $this->hasMany(\app\models\CompanyRate::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(\app\models\Contact::className(), ['default_company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactToCompanies()
    {
        return $this->hasMany(\app\models\ContactToCompany::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(\app\models\Item::className(), ['supplier_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(\app\models\Job::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRollouts()
    {
        return $this->hasMany(\app\models\Rollout::className(), ['company_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\CompanyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CompanyQuery(get_called_class());
    }

}
