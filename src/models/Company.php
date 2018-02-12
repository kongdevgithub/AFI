<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\models\workflow\CompanyWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkall\LinkAllBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "company".
 *
 * @mixin LinkBehavior
 * @mixin LinkAllBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property HubSpotCompany $hubSpotCompany
 *
 * @property Address[] $addresses
 * @property Address $billingAddress
 * @property Note[] $notes
 * @property User $staffRep
 */
class Company extends base\Company
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [CompanyWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [CompanyWorkflow::className(), 'afterChangeStatus']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = LinkAllBehavior::className();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'company',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
        ];
        //$behaviors['eav'] = [
        //    'class' => EavBehavior::className(),
        //    'modelAlias' => static::className(),
        //    'eavAttributesList' => [
        //        'delivery_docket_header' => [
        //            'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
        //        ],
        //    ],
        //];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = [
            'default' => ['id', 'name', 'phone', 'fax', 'website', 'status', 'staff_rep_id', 'price_structure_id', 'account_term_id', 'industry_id', 'job_type_id', 'default_contact_id', 'deleted_at', 'rates_encoded', 'merge_id', 'purchase_order_required', 'delivery_docket_header', 'first_job_due_date', 'last_job_due_date', 'excludes_tax', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'phone', 'fax', 'website', 'status', 'staff_rep_id', 'price_structure_id', 'account_term_id', 'industry_id', 'job_type_id', 'default_contact_id', 'deleted_at', 'rates_encoded', 'merge_id', 'purchase_order_required', 'delivery_docket_header', 'first_job_due_date', 'last_job_due_date', 'excludes_tax', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'phone', 'fax', 'website', 'status', 'staff_rep_id', 'price_structure_id', 'account_term_id', 'industry_id', 'job_type_id', 'default_contact_id', 'deleted_at', 'rates_encoded', 'merge_id', 'purchase_order_required', 'delivery_docket_header', 'first_job_due_date', 'last_job_due_date', 'excludes_tax', 'created_at', 'updated_at'],
        ];
        $scenarios['status'] = ['status'];
        $scenarios['merge'] = ['merge_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['website'], 'required'];
        $rules[] = [['website'], 'unique', 'targetAttribute' => ['website', 'deleted_at']];
        $rules[] = [['merge_id'], 'required', 'when' => function ($model) {
            /** @var static $model */
            return $model->scenario == 'merge';
        }];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['default_contact_id'] = Yii::t('app', 'Default Contact');
        $attributeLabels['staff_rep_id'] = Yii::t('app', 'Staff REP');
        $attributeLabels['price_structure_id'] = Yii::t('app', 'Price Structure');
        $attributeLabels['account_term_id'] = Yii::t('app', 'Account Terms');
        $attributeLabels['job_type_id'] = Yii::t('app', 'Job Type');
        $attributeLabels['industry_id'] = Yii::t('app', 'Industry');
        return $attributeLabels;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRates()
    {
        return $this->hasMany(CompanyRate::className(), ['company_id' => 'id'])
            ->andWhere('company_rate.deleted_at IS NULL');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contact::className(), ['id' => 'contact_id'])
            ->viaTable(ContactToCompany::tableName(), ['company_id' => 'id'])
            ->andWhere('contact.deleted_at IS NULL');
        //->inverseOf('companies');
        //->via('postToTag');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(Job::className(), ['company_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('company');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillingAddress()
    {
        $relation = Address::find()
            ->notDeleted()
            ->orOnCondition([
                'address.type' => Address::TYPE_BILLING,
                'address.model_id' => $this->id,
                'address.model_name' => get_class($this),
            ]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        $relation = Address::find()
            ->notDeleted()
            ->orOnCondition([
                'address.model_id' => $this->id,
                'address.model_name' => get_class($this),
            ]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        $relation = Note::find()
            ->notDeleted()
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => get_class($this),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffRep()
    {
        return $this->hasOne(User::className(), ['id' => 'staff_rep_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHubSpotCompany()
    {
        return $this->hasOne(HubSpotCompany::className(), ['model_id' => 'id']);
    }

    /**
     * @return query\LogQuery
     */
    public function getLogs()
    {
        $relation = Log::find();
        $relation->orOnCondition([
            'log.model_id' => $this->id,
            'log.model_name' => $this->className(),
        ]);
        $relation->orOnCondition([
            'log.model_id' => ArrayHelper::map($this->getAddresses()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Address::className(),
        ]);
        $relation->orOnCondition([
            'log.model_id' => ArrayHelper::map($this->getContacts()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Contact::className(),
        ]);
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails()
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => get_class($this),
        ]);
        $relation->orOnCondition([
            'audit_trail.model_id' => ArrayHelper::map($this->getAddresses()->where('1=1')->all(), 'id', 'id'),
            'audit_trail.model' => Address::className(),
        ]);
        $relation->orOnCondition([
            'audit_trail.model_id' => ArrayHelper::map($this->getContacts()->where('1=1')->all(), 'id', 'id'),
            'audit_trail.model' => Contact::className(),
        ]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->account_term_id === null)) {
            $this->account_term_id = AccountTerm::ACCOUNT_TERM_DEFAULT;
        }
        if ((!$skipIfSet || $this->price_structure_id === null)) {
            $this->price_structure_id = PriceStructure::PRICE_STRUCTURE_DEFAULT;
        }
        if ((!$skipIfSet || $this->job_type_id === null)) {
            $this->job_type_id = JobType::JOB_TYPE_DEFAULT;
        }
        if ((!$skipIfSet || $this->staff_rep_id === null)) {
            $this->staff_rep_id = Job::STAFF_LEAD_DEFAULT;
        }
        return $this;
    }

    /**
     * @return array
     *
     * rates_encoded example:
     * [
     *   {
     *     "product_type_id": 36, // ReFrame Skin
     *     "item_type_id": 100,   // Print
     *     "option_id": 5,        // Substrate
     *     "prices": {
     *       "11848": 50          // LUSTRE $50
     *     }
     *   }
     * ]
     */
    public function getRates()
    {
        if (!$this->rates_encoded) {
            return [];
        }
        return Json::decode($this->rates_encoded);
    }

    /**
     * @return bool
     */
    public function checkAccess()
    {
        if (Yii::$app->user->can('staff')) {
            return true;
        }
        $permissionName = '_company_' . $this->id;
        return Yii::$app->user->can($permissionName);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            'company-' . $this->id . ': ' . $this->name,
        ]);
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->default_contact_id) {
            $contactToCompany = ContactToCompany::find()
                ->notDeleted()
                ->andWhere([
                    'company_id' => $this->id,
                    'contact_id' => $this->default_contact_id,
                ])
                ->one();
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->company_id = $this->id;
                $contactToCompany->contact_id = $this->default_contact_id;
                $contactToCompany->save();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

}
