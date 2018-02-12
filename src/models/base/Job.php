<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "job".
 *
 * @property integer $id
 * @property string $vid
 * @property integer $fork_version_job_id
 * @property integer $copy_job_id
 * @property integer $redo_job_id
 * @property string $name
 * @property integer $job_type_id
 * @property integer $company_id
 * @property integer $contact_id
 * @property integer $staff_lead_id
 * @property integer $staff_rep_id
 * @property integer $staff_csr_id
 * @property integer $staff_designer_id
 * @property integer $rollout_id
 * @property integer $price_structure_id
 * @property integer $account_term_id
 * @property integer $production_days
 * @property string $due_date
 * @property string $status
 * @property integer $prebuild_days
 * @property integer $freight_days
 * @property string $production_date
 * @property string $prebuild_date
 * @property string $despatch_date
 * @property string $installation_date
 * @property string $purchase_order
 * @property string $quote_wholesale_price
 * @property integer $quote_generated
 * @property string $quote_total_cost
 * @property string $quote_factor
 * @property string $quote_class
 * @property string $quote_label
 * @property string $quote_factor_price
 * @property string $quote_markup
 * @property string $quote_retail_price
 * @property string $quote_weight
 * @property integer $deleted_at
 * @property integer $quote_at
 * @property integer $quote_lost_at
 * @property integer $production_pending_at
 * @property integer $production_at
 * @property integer $despatch_at
 * @property integer $complete_at
 * @property integer $feedback_at
 * @property string $quote_discount_price
 * @property string $quote_surcharge_price
 * @property string $quote_freight_price
 * @property string $quote_tax_price
 * @property string $quote_total_price
 * @property integer $quote_win_chance
 * @property string $quote_email_text
 * @property string $quote_greeting_text
 * @property string $quote_footer_text
 * @property string $quote_totals_format
 * @property string $quote_lost_reason
 * @property string $quote_maximum_discount_price
 * @property string $quote_template
 * @property string $artwork_email_text
 * @property string $artwork_greeting_text
 * @property string $invoice_sent
 * @property string $invoice_paid
 * @property string $invoice_reference
 * @property string $invoice_amount
 * @property integer $prebuild_required
 * @property string $delivery_instructions
 * @property string $freight_method
 * @property string $freight_notes
 * @property integer $packed_at
 * @property string $invoice_email_text
 * @property string $invoice_greeting_text
 * @property string $redo_reason
 * @property integer $excludes_tax
 * @property integer $installed_at
 * @property integer $freight_quote_requested_at
 * @property integer $freight_quote_provided_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\FeedbackToJob[] $feedbackToJobs
 * @property \app\models\AccountTerm $accountTerm
 * @property \app\models\Company $company
 * @property \app\models\Contact $contact
 * @property \app\models\JobType $jobType
 * @property \app\models\PriceStructure $priceStructure
 * @property \app\models\Rollout $rollout
 * @property \app\models\Job $forkVersionJob
 * @property \app\models\Job[] $jobs
 * @property \app\models\Job $copyJob
 * @property \app\models\Job[] $jobs0
 * @property \app\models\Job $redoJob
 * @property \app\models\Job[] $jobs1
 * @property \app\models\Product[] $products
 */
class Job extends ActiveRecord
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
        return 'job';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'vid', 'fork_version_job_id', 'copy_job_id', 'redo_job_id', 'name', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id', 'rollout_id', 'price_structure_id', 'account_term_id', 'production_days', 'due_date', 'status', 'prebuild_days', 'freight_days', 'production_date', 'prebuild_date', 'despatch_date', 'installation_date', 'purchase_order', 'quote_wholesale_price', 'quote_generated', 'quote_total_cost', 'quote_factor', 'quote_class', 'quote_label', 'quote_factor_price', 'quote_markup', 'quote_retail_price', 'quote_weight', 'deleted_at', 'quote_at', 'quote_lost_at', 'production_pending_at', 'production_at', 'despatch_at', 'complete_at', 'feedback_at', 'quote_discount_price', 'quote_surcharge_price', 'quote_freight_price', 'quote_tax_price', 'quote_total_price', 'quote_win_chance', 'quote_email_text', 'quote_greeting_text', 'quote_footer_text', 'quote_totals_format', 'quote_lost_reason', 'quote_maximum_discount_price', 'quote_template', 'artwork_email_text', 'artwork_greeting_text', 'invoice_sent', 'invoice_paid', 'invoice_reference', 'invoice_amount', 'prebuild_required', 'delivery_instructions', 'freight_method', 'freight_notes', 'packed_at', 'invoice_email_text', 'invoice_greeting_text', 'redo_reason', 'excludes_tax', 'installed_at', 'freight_quote_requested_at', 'freight_quote_provided_at', 'created_at', 'updated_at'],
            'create' => ['id', 'vid', 'fork_version_job_id', 'copy_job_id', 'redo_job_id', 'name', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id', 'rollout_id', 'price_structure_id', 'account_term_id', 'production_days', 'due_date', 'status', 'prebuild_days', 'freight_days', 'production_date', 'prebuild_date', 'despatch_date', 'installation_date', 'purchase_order', 'quote_wholesale_price', 'quote_generated', 'quote_total_cost', 'quote_factor', 'quote_class', 'quote_label', 'quote_factor_price', 'quote_markup', 'quote_retail_price', 'quote_weight', 'deleted_at', 'quote_at', 'quote_lost_at', 'production_pending_at', 'production_at', 'despatch_at', 'complete_at', 'feedback_at', 'quote_discount_price', 'quote_surcharge_price', 'quote_freight_price', 'quote_tax_price', 'quote_total_price', 'quote_win_chance', 'quote_email_text', 'quote_greeting_text', 'quote_footer_text', 'quote_totals_format', 'quote_lost_reason', 'quote_maximum_discount_price', 'quote_template', 'artwork_email_text', 'artwork_greeting_text', 'invoice_sent', 'invoice_paid', 'invoice_reference', 'invoice_amount', 'prebuild_required', 'delivery_instructions', 'freight_method', 'freight_notes', 'packed_at', 'invoice_email_text', 'invoice_greeting_text', 'redo_reason', 'excludes_tax', 'installed_at', 'freight_quote_requested_at', 'freight_quote_provided_at', 'created_at', 'updated_at'],
            'update' => ['id', 'vid', 'fork_version_job_id', 'copy_job_id', 'redo_job_id', 'name', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id', 'rollout_id', 'price_structure_id', 'account_term_id', 'production_days', 'due_date', 'status', 'prebuild_days', 'freight_days', 'production_date', 'prebuild_date', 'despatch_date', 'installation_date', 'purchase_order', 'quote_wholesale_price', 'quote_generated', 'quote_total_cost', 'quote_factor', 'quote_class', 'quote_label', 'quote_factor_price', 'quote_markup', 'quote_retail_price', 'quote_weight', 'deleted_at', 'quote_at', 'quote_lost_at', 'production_pending_at', 'production_at', 'despatch_at', 'complete_at', 'feedback_at', 'quote_discount_price', 'quote_surcharge_price', 'quote_freight_price', 'quote_tax_price', 'quote_total_price', 'quote_win_chance', 'quote_email_text', 'quote_greeting_text', 'quote_footer_text', 'quote_totals_format', 'quote_lost_reason', 'quote_maximum_discount_price', 'quote_template', 'artwork_email_text', 'artwork_greeting_text', 'invoice_sent', 'invoice_paid', 'invoice_reference', 'invoice_amount', 'prebuild_required', 'delivery_instructions', 'freight_method', 'freight_notes', 'packed_at', 'invoice_email_text', 'invoice_greeting_text', 'redo_reason', 'excludes_tax', 'installed_at', 'freight_quote_requested_at', 'freight_quote_provided_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fork_version_job_id', 'copy_job_id', 'redo_job_id', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id', 'staff_designer_id', 'rollout_id', 'price_structure_id', 'account_term_id', 'production_days', 'prebuild_days', 'freight_days', 'quote_generated', 'deleted_at', 'quote_at', 'quote_lost_at', 'production_pending_at', 'production_at', 'despatch_at', 'complete_at', 'feedback_at', 'quote_win_chance', 'prebuild_required', 'packed_at', 'excludes_tax', 'installed_at', 'freight_quote_requested_at', 'freight_quote_provided_at'], 'integer'],
            [['name', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id', 'price_structure_id', 'account_term_id', 'quote_class', 'quote_win_chance'], 'required'],
            [['due_date', 'production_date', 'prebuild_date', 'despatch_date', 'installation_date', 'invoice_sent', 'invoice_paid'], 'safe'],
            [['quote_wholesale_price', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_markup', 'quote_retail_price', 'quote_weight', 'quote_discount_price', 'quote_surcharge_price', 'quote_freight_price', 'quote_tax_price', 'quote_total_price', 'quote_maximum_discount_price', 'invoice_amount'], 'number'],
            [['quote_email_text', 'quote_greeting_text', 'quote_footer_text', 'artwork_email_text', 'artwork_greeting_text', 'delivery_instructions', 'invoice_email_text', 'invoice_greeting_text'], 'string'],
            [['vid', 'name', 'status', 'purchase_order', 'quote_class', 'quote_label', 'quote_totals_format', 'quote_lost_reason', 'quote_template', 'invoice_reference', 'freight_method', 'freight_notes', 'redo_reason'], 'string', 'max' => 255],
            [['account_term_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\AccountTerm::className(), 'targetAttribute' => ['account_term_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Contact::className(), 'targetAttribute' => ['contact_id' => 'id']],
            [['job_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\JobType::className(), 'targetAttribute' => ['job_type_id' => 'id']],
            [['price_structure_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\PriceStructure::className(), 'targetAttribute' => ['price_structure_id' => 'id']],
            [['rollout_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Rollout::className(), 'targetAttribute' => ['rollout_id' => 'id']],
            [['fork_version_job_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Job::className(), 'targetAttribute' => ['fork_version_job_id' => 'id']],
            [['copy_job_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Job::className(), 'targetAttribute' => ['copy_job_id' => 'id']],
            [['redo_job_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Job::className(), 'targetAttribute' => ['redo_job_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'vid' => Yii::t('models', 'Vid'),
            'fork_version_job_id' => Yii::t('models', 'Fork Version Job ID'),
            'copy_job_id' => Yii::t('models', 'Copy Job ID'),
            'redo_job_id' => Yii::t('models', 'Redo Job ID'),
            'name' => Yii::t('models', 'Name'),
            'job_type_id' => Yii::t('models', 'Job Type ID'),
            'company_id' => Yii::t('models', 'Company ID'),
            'contact_id' => Yii::t('models', 'Contact ID'),
            'staff_lead_id' => Yii::t('models', 'Staff Lead ID'),
            'staff_rep_id' => Yii::t('models', 'Staff Rep ID'),
            'staff_csr_id' => Yii::t('models', 'Staff Csr ID'),
            'staff_designer_id' => Yii::t('models', 'Staff Designer ID'),
            'rollout_id' => Yii::t('models', 'Rollout ID'),
            'price_structure_id' => Yii::t('models', 'Price Structure ID'),
            'account_term_id' => Yii::t('models', 'Account Term ID'),
            'production_days' => Yii::t('models', 'Production Days'),
            'due_date' => Yii::t('models', 'Due Date'),
            'status' => Yii::t('models', 'Status'),
            'prebuild_days' => Yii::t('models', 'Prebuild Days'),
            'freight_days' => Yii::t('models', 'Freight Days'),
            'production_date' => Yii::t('models', 'Production Date'),
            'prebuild_date' => Yii::t('models', 'Prebuild Date'),
            'despatch_date' => Yii::t('models', 'Despatch Date'),
            'installation_date' => Yii::t('models', 'Installation Date'),
            'purchase_order' => Yii::t('models', 'Purchase Order'),
            'quote_wholesale_price' => Yii::t('models', 'Quote Wholesale Price'),
            'quote_generated' => Yii::t('models', 'Quote Generated'),
            'quote_total_cost' => Yii::t('models', 'Quote Total Cost'),
            'quote_factor' => Yii::t('models', 'Quote Factor'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'quote_label' => Yii::t('models', 'Quote Label'),
            'quote_factor_price' => Yii::t('models', 'Quote Factor Price'),
            'quote_markup' => Yii::t('models', 'Quote Markup'),
            'quote_retail_price' => Yii::t('models', 'Quote Retail Price'),
            'quote_weight' => Yii::t('models', 'Quote Weight'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'quote_at' => Yii::t('models', 'Quote At'),
            'quote_lost_at' => Yii::t('models', 'Quote Lost At'),
            'production_pending_at' => Yii::t('models', 'Production Pending At'),
            'production_at' => Yii::t('models', 'Production At'),
            'despatch_at' => Yii::t('models', 'Despatch At'),
            'complete_at' => Yii::t('models', 'Complete At'),
            'feedback_at' => Yii::t('models', 'Feedback At'),
            'quote_discount_price' => Yii::t('models', 'Quote Discount Price'),
            'quote_surcharge_price' => Yii::t('models', 'Quote Surcharge Price'),
            'quote_freight_price' => Yii::t('models', 'Quote Freight Price'),
            'quote_tax_price' => Yii::t('models', 'Quote Tax Price'),
            'quote_total_price' => Yii::t('models', 'Quote Total Price'),
            'quote_win_chance' => Yii::t('models', 'Quote Win Chance'),
            'quote_email_text' => Yii::t('models', 'Quote Email Text'),
            'quote_greeting_text' => Yii::t('models', 'Quote Greeting Text'),
            'quote_footer_text' => Yii::t('models', 'Quote Footer Text'),
            'quote_totals_format' => Yii::t('models', 'Quote Totals Format'),
            'quote_lost_reason' => Yii::t('models', 'Quote Lost Reason'),
            'quote_maximum_discount_price' => Yii::t('models', 'Quote Maximum Discount Price'),
            'quote_template' => Yii::t('models', 'Quote Template'),
            'artwork_email_text' => Yii::t('models', 'Artwork Email Text'),
            'artwork_greeting_text' => Yii::t('models', 'Artwork Greeting Text'),
            'invoice_sent' => Yii::t('models', 'Invoice Sent'),
            'invoice_paid' => Yii::t('models', 'Invoice Paid'),
            'invoice_reference' => Yii::t('models', 'Invoice Reference'),
            'invoice_amount' => Yii::t('models', 'Invoice Amount'),
            'prebuild_required' => Yii::t('models', 'Prebuild Required'),
            'delivery_instructions' => Yii::t('models', 'Delivery Instructions'),
            'freight_method' => Yii::t('models', 'Freight Method'),
            'freight_notes' => Yii::t('models', 'Freight Notes'),
            'packed_at' => Yii::t('models', 'Packed At'),
            'invoice_email_text' => Yii::t('models', 'Invoice Email Text'),
            'invoice_greeting_text' => Yii::t('models', 'Invoice Greeting Text'),
            'redo_reason' => Yii::t('models', 'Redo Reason'),
            'excludes_tax' => Yii::t('models', 'Excludes Tax'),
            'installed_at' => Yii::t('models', 'Installed At'),
            'freight_quote_requested_at' => Yii::t('models', 'Freight Quote Requested At'),
            'freight_quote_provided_at' => Yii::t('models', 'Freight Quote Provided At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbackToJobs()
    {
        return $this->hasMany(\app\models\FeedbackToJob::className(), ['job_id' => 'id']);
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
    public function getCompany()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'company_id']);
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
    public function getRollout()
    {
        return $this->hasOne(\app\models\Rollout::className(), ['id' => 'rollout_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForkVersionJob()
    {
        return $this->hasOne(\app\models\Job::className(), ['id' => 'fork_version_job_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(\app\models\Job::className(), ['fork_version_job_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCopyJob()
    {
        return $this->hasOne(\app\models\Job::className(), ['id' => 'copy_job_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs0()
    {
        return $this->hasMany(\app\models\Job::className(), ['copy_job_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRedoJob()
    {
        return $this->hasOne(\app\models\Job::className(), ['id' => 'redo_job_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs1()
    {
        return $this->hasMany(\app\models\Job::className(), ['redo_job_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\models\Product::className(), ['job_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\JobQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\JobQuery(get_called_class());
    }

}
