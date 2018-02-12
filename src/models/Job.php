<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\GearmanManager;
use app\components\Helper;
use app\components\PdfManager;
use app\components\quotes\jobs\TieredJobQuote;
use app\models\validator\JobQuoteDiscountPriceValidator;
use app\models\validator\JobQuoteLostReasonValidator;
use app\models\workflow\JobWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use app\components\ReturnUrl;
use cornernote\softdelete\SoftDeleteBehavior;
use mar\eav\behaviors\EavBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "job".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property int $vid
 * @property float $quote_factor
 * @property float $quote_total_cost
 * @property float $quote_wholesale_price
 * @property float $quote_factor_price
 * @property float $quote_markup
 * @property float $quote_retail_price
 * @property float $quote_discount_price
 * @property float $quote_freight_price
 * @property float $quote_surcharge_price
 * @property float $quote_tax_price
 * @property float $quote_total_price
 * @property float $quote_weight
 * @property float $quote_maximum_discount_price
 *
 * @property User $staffLead
 * @property User $staffRep
 * @property User $staffCsr
 * @property User $staffDesigner
 * @property Address[] $addresses
 * @property Address[] $shippingAddresses
 * @property Address $billingAddress
 * @property Attachment[] $attachments
 * @property Link[] $links
 * @property Note[] $notes
 * @property Notification[] $notifications
 * @property Package[] $packages
 * @property Job[] $forkVersionJobs
 * @property Job[] $copyJobs
 * @property Job[] $redoJobs
 * @property HubSpotDeal $hubSpotDeal
 * @property DearSale $dearSale
 * @property string $quote_approved_by
 * @property Attachment[] $quotePdfs
 * @property string $gearman_quote
 * @property string $gearman_product_import
 * @property array $product_imports_pending
 * @property array $product_imports_complete
 * @property string $dear_mode
 * @property string $dear_materials_hash
 */
class Job extends base\Job
{
    /**
     *
     */
    const STAFF_LEAD_DEFAULT = 12;

    /**
     * @var bool
     */
    public $apply_discount_to_products;

    /**
     * @var bool
     */
    public $allow_excessive_discount;

    /**
     * @var bool
     */
    public $allow_early_due;

    /**
     * @var bool
     */
    public $send_email;

    /**
     * @var string
     */
    public $correction_reason;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [JobWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [JobWorkflow::className(), 'afterChangeStatus']);
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
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'job',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
        ];
        $behaviors['eav'] = [
            'class' => EavBehavior::className(),
            'modelAlias' => static::className(),
            'eavAttributesList' => [
                'quote_approved_by' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'gearman_quote' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'gearman_product_import' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'product_imports_pending' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'product_imports_complete' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'dear_mode' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'dear_materials_hash' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
            ],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['quote_lost_reason'], JobQuoteLostReasonValidator::className()];
        $rules[] = [['quote_discount_price'], JobQuoteDiscountPriceValidator::className()];
        $rules[] = [['apply_discount_to_products', 'allow_excessive_discount', 'allow_early_due', 'send_email'], 'safe'];
        $rules[] = [['quote_surcharge_price', 'quote_discount_price', 'production_days', 'freight_days'], 'number', 'min' => 0];
        $rules[] = [['correction_reason'], 'required', 'when' => function ($model) {
            /** @var Job $model */
            return $model->getChangedAlertEmails() ? true : false;
        }];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['status'] = ['status', 'quote_lost_reason', 'due_date', 'production_days', 'prebuild_days', 'freight_days', 'allow_excessive_discount', 'allow_early_due', 'send_email', 'quote_email_text'];
        $scenarios['discount'] = ['quote_discount_price', 'apply_discount_to_products'];
        $scenarios['freight'] = ['quote_freight_price', 'freight_notes', 'freight_method'];
        $scenarios['surcharge'] = ['quote_surcharge_price'];
        $scenarios['due'] = ['due_date', 'production_days', 'prebuild_days', 'freight_days', 'correction_reason', 'installation_date'];
        $scenarios['finance'] = ['quote_freight_price', 'quote_surcharge_price', 'quote_discount_price', 'invoice_sent', 'invoice_amount', 'invoice_reference', 'invoice_paid'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['company_id'] = Yii::t('app', 'Company');
        $attributeLabels['rollout_id'] = Yii::t('app', 'Rollout');
        $attributeLabels['contact_id'] = Yii::t('app', 'Contact');
        $attributeLabels['staff_lead_id'] = Yii::t('app', 'BDM');
        $attributeLabels['staff_rep_id'] = Yii::t('app', 'Account Manager');
        $attributeLabels['staff_csr_id'] = Yii::t('app', 'CSR');
        $attributeLabels['staff_designer_id'] = Yii::t('app', 'Designer');
        $attributeLabels['price_structure_id'] = Yii::t('app', 'Price Structure');
        $attributeLabels['account_term_id'] = Yii::t('app', 'Account Terms');
        $attributeLabels['job_type_id'] = Yii::t('app', 'Job Type');
        $attributeLabels['fork_version_job_id'] = Yii::t('app', 'Forked From');
        $attributeLabels['copy_job_id'] = Yii::t('app', 'Copied From');
        $attributeLabels['redo_job_id'] = Yii::t('app', 'Redone From');
        $attributeLabels['quote_footer_text'] = Yii::t('app', 'Quote Closing Text');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // update totals
        if (in_array($this->scenario, ['freight', 'discount', 'surcharge'])) {
            $totalEx = $this->quote_retail_price + $this->quote_freight_price + $this->quote_surcharge_price - $this->quote_discount_price;
            $this->quote_tax_price = $totalEx * 0.1;
            $this->quote_total_price = $totalEx + $this->quote_tax_price;
        }

        // set the *_at dates
        if ($insert || $this->isAttributeChanged('status')) {
            $date = time();
            if ($this->status == 'job/draft') {
                $this->quote_at = null;
                $this->quote_lost_at = null;
                $this->production_pending_at = null;
                $this->production_at = null;
                $this->despatch_at = null;
                $this->packed_at = null;
                $this->complete_at = null;
            }
            if ($this->status == 'job/quote') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
            }
            if ($this->status == 'job/quoteLost') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->quote_lost_at)
                    $this->quote_lost_at = $date;
            }
            if ($this->status == 'job/productionPending') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->production_pending_at)
                    $this->production_pending_at = $date;
            }
            if ($this->status == 'job/production') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->production_at)
                    $this->production_at = $date;
            }
            if ($this->status == 'job/despatch') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
            }
            if ($this->status == 'job/packed') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
            }
            if ($this->status == 'job/complete') {
                if (!$this->quote_at)
                    $this->quote_at = $date;
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
                if (!$this->complete_at)
                    $this->complete_at = $date;
            }
        }

        // set the *_date dates
        if ($this->scenario == 'due'
            || $this->isAttributeChanged('due_date')
            || $this->isAttributeChanged('production_days')
            || $this->isAttributeChanged('prebuild_days')
            || $this->isAttributeChanged('freight_days')
        ) {
            $this->despatch_date = Helper::getRelativeDate($this->due_date, $this->freight_days * -1, false);
            $this->prebuild_date = Helper::getRelativeDate($this->despatch_date, $this->prebuild_days * -1);
            $this->production_date = Helper::getRelativeDate($this->prebuild_date, $this->production_days * -1);
        }

        // unset lost reason
        if ($this->status != 'job/quoteLost') {
            $this->quote_lost_reason = null;
        }

        // sanitize data
        $this->quote_retail_price = round($this->quote_retail_price, 4);
        $this->quote_factor_price = round($this->quote_factor_price, 8);
        $this->quote_wholesale_price = round($this->quote_wholesale_price, 4);
        $this->quote_freight_price = round($this->quote_freight_price, 4);
        $this->quote_surcharge_price = round($this->quote_surcharge_price, 4);
        $this->quote_discount_price = round($this->quote_discount_price, 4);
        $this->quote_total_price = round($this->quote_total_price, 4);
        $this->quote_tax_price = round($this->quote_tax_price, 4);
        $this->quote_maximum_discount_price = round($this->quote_maximum_discount_price, 4);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // add default address
        if ($insert || !$this->billingAddress) {
            if ($this->company_id) {
                $company = Company::findOne($this->company_id);
                if ($company->billingAddress) {
                    $company->billingAddress->copy([
                        'Address' => [
                            'model_name' => $this->className(),
                            'model_id' => $this->id,
                        ]
                    ]);
                }
            }
        }

        // save quote as attachment
        if (!$insert && isset($changedAttributes['status'])) {
            if ($changedAttributes['status'] == 'job/draft') {
                $this->attachQuote();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     *
     */
    public function attachQuote()
    {
        $s3 = Yii::$app->s3;
        $filename = Inflector::slug($this->company->name) . '_' . Inflector::slug($this->name) . '_' . $this->vid . '_' . date('Ymdhis');
        $tmpFile = Yii::$app->runtimePath . '/quote/' . $filename . '.pdf';
        FileHelper::createDirectory(dirname($tmpFile));
        PdfManager::getJobQuote($this)->saveAs($tmpFile);
        if (!file_exists($tmpFile)) {
            return false;
        }

        $attachment = new Attachment();
        $attachment->model_name = $this->className() . '-quote';
        $attachment->model_id = $this->id;
        $attachment->filename = $filename;
        $attachment->extension = 'pdf';
        $attachment->filetype = 'application/pdf';
        $attachment->notes = 'Archived Quote PDF';
        $attachment->filesize = filesize($tmpFile);

        $fileSrc = $attachment->getFileSrc();
        $i = 0;
        while ($s3->exist($fileSrc)) {
            $i++;
            $attachment->filename .= '-' . $i;
            $fileSrc = $attachment->getFileSrc();
        }
        $localFile = Yii::$app->runtimePath . '/' . $fileSrc;
        FileHelper::createDirectory(dirname($localFile));
        rename($tmpFile, $localFile);

        Yii::$app->s3->upload($fileSrc, $localFile);
        $attachment->save(false);
        return $attachment->thumb();
    }

    /**
     * @param bool $resetProducts
     * @param bool $spoolQuote
     * @throws Exception
     */
    public function resetQuoteGenerated($resetProducts = true, $spoolQuote = true)
    {
        $this->quote_generated = 0;
        if (!$this->save(false)) {
            throw new Exception('Cannot save job-' . $this->id . ': ' . Helper::getErrorString($this));
        }
        if ($resetProducts) {
            foreach ($this->products as $product) {
                $product->resetQuoteGenerated();
            }
        }
        if ($spoolQuote) {
            $this->spoolQuote();
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->products as $product) {
            $product->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForkVersionJobs()
    {
        return $this->hasMany(Job::className(), ['fork_version_job_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('forkVersionJob');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCopyJobs()
    {
        return $this->hasMany(Job::className(), ['copy_job_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('copyJob');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRedoJobs()
    {
        return $this->hasMany(Job::className(), ['redo_job_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('redoJob');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['job_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->andWhere('fork_quantity_product_id IS NULL')
            ->orderBy([
                'sort_order' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->inverseOf('job');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        $relation = Address::find()
            ->andWhere('address.deleted_at IS NULL')
            ->orOnCondition([
                'address.model_id' => $this->id,
                'address.model_name' => $this->className(),
            ])
            ->orderBy(['type' => SORT_ASC, 'name' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShippingAddresses()
    {
        $relation = Address::find()
            ->andWhere('address.deleted_at IS NULL')
            ->orOnCondition([
                'address.model_id' => $this->id,
                'address.model_name' => $this->className(),
                'address.type' => Address::TYPE_SHIPPING,
            ]);
        //->orderBy(['name' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillingAddress()
    {
        $relation = Address::find()
            ->andWhere('address.deleted_at IS NULL')
            ->orOnCondition([
                'address.type' => Address::TYPE_BILLING,
                'address.model_id' => $this->id,
                'address.model_name' => $this->className(),
            ]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuotePdfs()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className() . '-quote',
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
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinks()
    {
        $relation = Link::find()
            ->andWhere('link.deleted_at IS NULL')
            ->orOnCondition([
                'link.model_id' => $this->id,
                'link.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        $relation = Notification::find()
            ->andWhere('notification.deleted_at IS NULL')
            ->orOnCondition([
                'notification.model_id' => $this->id,
                'notification.model_name' => $this->className(),
            ])->orderBy(['created_at' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
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
            'log.model_id' => ArrayHelper::map($this->getProducts()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Product::className(),
        ]);
        foreach ($this->products as $product) {
            $relation->orOnCondition([
                'log.model_id' => ArrayHelper::map($product->getItems()->where('1=1')->all(), 'id', 'id'),
                'log.model_name' => Item::className(),
            ]);
            foreach ($product->items as $item) {
                $relation->orOnCondition([
                    'log.model_id' => ArrayHelper::map($item->getUnits()->where('1=1')->all(), 'id', 'id'),
                    'log.model_name' => Unit::className(),
                ]);
            }
        }
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @param array $relations
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails($relations = [])
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => $this->className(),
        ]);
        if (in_array(Product::className(), $relations)) {
            /** @var Product[] $products */
            $products = $this->getProducts()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($products, 'id', 'id'),
                'audit_trail.model' => Product::className(),
            ]);
            if (in_array(Item::className(), $relations)) {
                foreach ($products as $product) {
                    /** @var Item[] $items */
                    $items = $product->getItems()->where('1=1')->all();
                    $relation->orOnCondition([
                        'audit_trail.model_id' => ArrayHelper::map($items, 'id', 'id'),
                        'audit_trail.model' => Item::className(),
                    ]);
                    if (in_array(Unit::className(), $relations)) {
                        foreach ($items as $item) {
                            /** @var Unit[] $units */
                            $units = $item->getUnits()->where('1=1')->all();
                            $relation->orOnCondition([
                                'audit_trail.model_id' => ArrayHelper::map($units, 'id', 'id'),
                                'audit_trail.model' => Unit::className(),
                            ]);
                        }
                    }
                }
            }
            if (in_array(ProductToOption::className(), $relations)) {
                foreach ($products as $product) {
                    /** @var ProductToOption[] $productToOptions */
                    $productToOptions = $product->getProductToOptions()->where('1=1')->all();
                    $relation->orOnCondition([
                        'audit_trail.model_id' => ArrayHelper::map($productToOptions, 'id', 'id'),
                        'audit_trail.model' => ProductToOption::className(),
                    ]);
                }
            }
            if (in_array(ProductToComponent::className(), $relations)) {
                foreach ($products as $product) {
                    /** @var ProductToComponent[] $productToComponents */
                    $productToComponents = $product->getProductToComponents()->where('1=1')->all();
                    $relation->orOnCondition([
                        'audit_trail.model_id' => ArrayHelper::map($productToComponents, 'id', 'id'),
                        'audit_trail.model' => ProductToComponent::className(),
                    ]);
                }
            }
        }
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffLead()
    {
        return $this->hasOne(User::className(), ['id' => 'staff_lead_id']);
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
    public function getStaffCsr()
    {
        return $this->hasOne(User::className(), ['id' => 'staff_csr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffDesigner()
    {
        return $this->hasOne(User::className(), ['id' => 'staff_designer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHubSpotDeal()
    {
        return $this->hasOne(HubSpotDeal::className(), ['model_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDearSale()
    {
        return $this->hasOne(DearSale::className(), ['model_id' => 'id']);
    }

    /**
     * @param bool $showInactiveMain
     * @return string
     */
    public function getStatusButtons($showInactiveMain = false)
    {
        if (in_array($this->status, ['job/production', 'job/despatch', 'job/packed'])) {
            $button = '';
            if ($showInactiveMain) {
                $button = $this->getStatusButton() . '&nbsp;';
            }
            return $button . Helper::getStatusButtonGroup($this->getStatusList());
        }
        return $this->getStatusButton();
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        $cacheKey = 'getStatusList';
        $statusList = $this->getCache($cacheKey, true);
        if ($statusList) return $statusList;

        $statusList = [];
        if (in_array($this->status, ['job/production', 'job/despatch', 'job/packed'])) {
            foreach ($this->products as $product) {
                foreach ($product->getStatusList() as $status => $quantity) {
                    $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
                }
            }
        } else {
            $status = $this->status;
            $quantity = 0; //$this->quantity;
            $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
        }
        //JobSortHelper::sortStatus($statusList);
        return $this->setCache($cacheKey, $statusList, true);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Html::a('job-' . $this->vid, ['/job/view', 'id' => $this->id, 'ru' => ReturnUrl::getToken()], ['class' => 'label label-default']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            '#' . $this->vid . ': ' . $this->name,
            $this->company->name,
        ]);
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return 'job-' . $this->vid;
    }

    /**
     * @return int
     */
    public function generateVid()
    {
        $topParent = $this->getForkTopParent();
        $versions = $topParent->getForkVersionVids();
        if (count($versions) > 1 && isset($versions[$this->id])) {
            return $versions[$this->id];
        }
        return (string)$this->id;
    }


    /**
     * @return array
     */
    public function getForkVersionVids()
    {
        $versionVids = [];
        $topParent = $this->getForkTopParent();
        $versionVids[$topParent->id] = $topParent->id . 'v1';
        $versions = $topParent->getForkVersionIds();
        foreach ($versions as $k => $job_id) {
            $versionVids[$job_id] = $topParent->id . 'v' . ($k + 2);
        }
        return $versionVids;
    }

    /**
     * @return $this
     */
    public function getForkTopParent()
    {
        if ($this->forkVersionJob) {
            return $this->forkVersionJob->getForkTopParent();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getForkVersionIds()
    {
        $forkVersions = [];
        foreach ($this->forkVersionJobs as $forkVersionJob) {
            $forkVersions[$forkVersionJob->id] = $forkVersionJob->id;
            if ($forkVersionJob->forkVersionJobs) {
                $forkVersions = ArrayHelper::merge($forkVersions, $forkVersionJob->getForkVersionIds());
            }
        }
        sort($forkVersions);
        return $forkVersions;
    }

    /**
     * @return Package[]
     */
    public function getPackages()
    {
        $packages = $this->getCache('getPackages');
        if ($packages !== false) {
            return $packages;
        }
        $packages = [];
        foreach ($this->products as $product) {
            foreach ($product->items as $item) {
                foreach ($item->units as $unit) {
                    if ($unit->package) {
                        $packages[$unit->package->id] = $unit->package;
                        foreach ($unit->package->overflowPackages as $overflowPackage) {
                            $packages[$overflowPackage->id] = $overflowPackage;
                        }
                    }
                }
            }
        }
        krsort($packages);
        return $this->setCache('getPackages', $packages);
    }


    /**
     * @return Pickup[]
     */
    public function getPickups()
    {
        $pickups = $this->getCache('getPickups');
        if ($pickups !== false) {
            return $pickups;
        }
        $pickups = [];
        $packages = $this->getPackages();
        foreach ($packages as $package) {
            if ($package->pickup) {
                $pickups[$package->pickup->id] = $package->pickup;
            }
        }
        return $this->setCache('getPickups', $pickups);
    }

    /**
     *
     */
    public function spoolQuote()
    {
        if (!$this->getSpoolQuoteStatus()) {
            $this->gearman_quote = GearmanManager::runJobQuote($this->id);
            $this->save(false);
        }
    }

    /**
     * @return bool
     */
    public function getSpoolQuoteStatus()
    {
        if ($this->gearman_quote) {
            $stat = GearmanManager::getBackgroundStatus(Yii::$app->gearman, $this->gearman_quote);
            if ($stat[0]) {
                return true;
            }
            $this->gearman_quote = null;
            $this->save(false);
        }
        return false;
    }

    /**
     *
     */
    public function spoolProductImport()
    {
        if (!$this->getSpoolProductImportStatus()) {
            $this->gearman_product_import = GearmanManager::runJobProductImport($this->id);
            $this->save(false);
        }
    }

    /**
     * @return bool
     */
    public function getSpoolProductImportStatus()
    {
        if ($this->gearman_product_import) {
            $stat = GearmanManager::getBackgroundStatus(Yii::$app->gearman, $this->gearman_product_import);
            if ($stat[0]) {
                return true;
            }
            $this->gearman_product_import = null;
            $this->save(false);
        }
        return false;
    }

    /**
     * @return array
     */
    public static function optsQuoteWinChance()
    {
        return [
            '75' => Yii::t('app', 'High'),
            '50' => Yii::t('app', 'Medium'),
            '25' => Yii::t('app', 'Low'),
        ];
    }

    /**
     * @return array
     */
    public static function optsQuoteLostReason()
    {
        return [
            'alternative' => 'Chose alternative AFI solution',
            'price' => 'Price – too expensive',
            'client' => 'Client - project did not go ahead',
            'deadline' => 'Deadline - Unable to meet client deadline',
            'phantom' => 'Test Quote',
        ];
        //return [
        //    'phantom' => 'Phantom - not real/enquiry/merged/obsolete',
        //    'client' => 'Client - job not proceeding',
        //    'price' => 'Price - too expensive',
        //    'product' => 'Product - choose alternative',
        //    'specs' => 'Product - product did not meet specs',
        //    'other' => 'Unclear',
        //];
    }

    /**
     * @return array
     */
    public static function optsQuoteTotalsFormat()
    {
        return [
            'hide-totals' => Yii::t('app', 'Hide Totals'),
            'hide-product-prices' => Yii::t('app', 'Hide Product Prices'),
            'show-product-discounts' => Yii::t('app', 'Show Product Discounts'),
        ];
    }

    /**
     * @return array
     */
    public static function optsQuoteTemplate()
    {
        return [
            'afi' => Yii::t('app', 'AFI'),
            'octanorm' => Yii::t('app', 'Octanorm'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        $company = ($this->company_id ? Company::findOne($this->company_id) : false);
        $identity = Yii::$app->user->isGuest ? false : Yii::$app->user->identity;

        if ((!$skipIfSet || $this->quote_class === null)) {
            $this->quote_class = TieredJobQuote::className();
        }
        if ((!$skipIfSet || $this->quote_email_text === null)) {
            $this->quote_email_text = $identity && $identity->quote_email_text ? $identity->quote_email_text : null;
            if (!$this->quote_email_text) {
                $this->quote_email_text = Yii::$app->settings->get('quoteEmailText', 'app');
            }
        }
        if ((!$skipIfSet || $this->quote_greeting_text === null)) {
            $this->quote_greeting_text = $identity && $identity->quote_greeting_text ? $identity->quote_greeting_text : null;
            if (!$this->quote_greeting_text) {
                $this->quote_greeting_text = Yii::$app->settings->get('quoteGreetingText', 'app');
            }
        }
        if ((!$skipIfSet || $this->quote_footer_text === null)) {
            $this->quote_footer_text = $identity && $identity->quote_footer_text ? $identity->quote_footer_text : null;
        }
        if ((!$skipIfSet || $this->quote_template === null)) {
            $this->quote_template = $identity && $identity->quote_template ? $identity->quote_template : null;
        }
        if ((!$skipIfSet || $this->quote_totals_format === null)) {
            $this->quote_totals_format = $identity && $identity->quote_totals_format ? $identity->quote_totals_format : null;
        }
        if ((!$skipIfSet || $this->artwork_email_text === null)) {
            $this->artwork_email_text = Yii::$app->settings->get('artworkEmailText', 'app');
        }
        if ((!$skipIfSet || $this->artwork_greeting_text === null)) {
            $this->artwork_greeting_text = Yii::$app->settings->get('artworkGreetingText', 'app');
        }
        if ((!$skipIfSet || $this->invoice_email_text === null)) {
            $this->invoice_email_text = Yii::$app->settings->get('invoiceEmailText', 'app');
        }
        if ((!$skipIfSet || $this->invoice_greeting_text === null)) {
            $this->invoice_greeting_text = Yii::$app->settings->get('invoiceGreetingText', 'app');
        }
        if ((!$skipIfSet || $this->account_term_id === null)) {
            $this->account_term_id = $company ? $company->account_term_id : AccountTerm::ACCOUNT_TERM_DEFAULT;
        }
        if ((!$skipIfSet || $this->price_structure_id === null)) {
            $this->price_structure_id = $company ? $company->price_structure_id : PriceStructure::PRICE_STRUCTURE_DEFAULT;
        }
        if ((!$skipIfSet || $this->job_type_id === null)) {
            $this->job_type_id = $company ? $company->job_type_id : JobType::JOB_TYPE_DEFAULT;
        }
        if ((!$skipIfSet || $this->staff_lead_id === null)) {
            $this->staff_lead_id = $company && $company->staff_rep_id ? $company->staff_rep_id : Job::STAFF_LEAD_DEFAULT;
        }
        if ((!$skipIfSet || $this->staff_rep_id === null)) {
            $this->staff_rep_id = $company && $company->staff_rep_id ? $company->staff_rep_id : Job::STAFF_LEAD_DEFAULT;
        }
        if ((!$skipIfSet || $this->staff_csr_id === null)) {
            $this->staff_csr_id = Yii::$app->user->id;
        }
        if ((!$skipIfSet || $this->excludes_tax === null)) {
            $this->excludes_tax = $company && $company->excludes_tax ? 1 : 0;
        }
        if ((!$skipIfSet || $this->quote_template === null)) {
            $this->quote_template = 'afi';
        }
        if ((!$skipIfSet || $this->production_days === null)) {
            $this->production_days = 3;
        }
        if ((!$skipIfSet || $this->freight_days === null)) {
            $this->freight_days = 3;
        }
        if ((!$skipIfSet || $this->prebuild_days === null)) {
            $this->prebuild_days = 0;
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @return Job|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $job = new Job();
        $job->loadDefaultValues();
        $job->attributes = $this->attributes;
        $job->id = null;
        if (isset($attributes['Job']['status'])) {
            $job->status = $attributes['Job']['status'];
            $job->initStatus();
        } else {
            $job->status = 'job/draft';
        }
        $allowedAttributes = [
            'due_date',
            'quote_at',
            'production_at',
            'despatch_at',
            'complete_at',
            'packed_at',
            'installed_at',
            'copy_job_id',
            'redo_job_id',
            'fork_version_job_id',
            'status',
        ];
        if (!empty($attributes['Job'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Job'])) {
                    debug($attribute);
                    $job->$attribute = $attributes['Job'][$attribute];
                }
            }
        }
        if (!$job->save()) {
            throw new Exception('cannot copy Job-' . $this->id . ': ' . Helper::getErrorString($job));
        }
        if ($job->billingAddress) {
            $job->billingAddress->delete();
        }
        foreach ($this->products as $_product) {
            $product = $_product->copy([
                'Product' => [
                    'job_id' => $job->id,
                ],
            ]);
        }
        foreach ($this->notes as $_note) {
            $note = $_note->copy([
                'Note' => [
                    'model_name' => $this->className(),
                    'model_id' => $job->id,
                ],
            ]);
        }
        foreach ($this->links as $_link) {
            $link = $_link->copy([
                'Link' => [
                    'model_name' => $this->className(),
                    'model_id' => $job->id,
                ],
            ]);
        }
        foreach ($this->attachments as $_attachment) {
            $attachment = $_attachment->copy([
                'Attachment' => [
                    'model_name' => $this->className(),
                    'model_id' => $job->id,
                ],
            ]);
        }
        foreach ($this->addresses as $_address) {
            $address = $_address->copy([
                'Address' => [
                    'model_name' => $this->className(),
                    'model_id' => $job->id,
                ],
            ]);
        }
        return $job;
    }

    /**
     * @return string
     */
    public function getQuoteEmailHtml()
    {
        return strtr($this->quote_email_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
            '{approval_url}' => Url::to(['//approval/quote', 'id' => $this->id, 'key' => md5($this->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))], 'https'),
            '{quote_label}' => $this->getTitle(),
        ]);
    }

    /**
     * @return string
     */
    public function getQuoteGreetingHtml()
    {
        return Yii::$app->formatter->asNtext(strtr($this->quote_greeting_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
        ]));
    }

    /**
     * @return string
     */
    public function getArtworkEmailHtml()
    {
        return strtr($this->artwork_email_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
            '{approval_url}' => Url::to(['//approval/artwork', 'id' => $this->id, 'key' => md5($this->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))], 'https'),
            '{job_label}' => $this->getTitle(),
        ]);
    }

    /**
     * @return string
     */
    public function getArtworkGreetingHtml()
    {
        return Yii::$app->formatter->asNtext(strtr($this->artwork_greeting_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
        ]));
    }

    /**
     * @return string
     */
    public function getInvoiceEmailHtml()
    {
        return strtr($this->invoice_email_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
            '{job_label}' => $this->getTitle(),
            '{due_date}' => Yii::$app->formatter->asDate($this->due_date),
        ]);
    }

    /**
     * @return string
     */
    public function getInvoiceGreetingHtml()
    {
        return Yii::$app->formatter->asNtext(strtr($this->invoice_greeting_text, [
            '{contact_first_name}' => $this->contact->first_name,
            '{staff_rep_name}' => $this->staffRep->label,
            '{staff_rep_phone}' => $this->staffRep->profile->phone ? $this->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
            '{job_label}' => $this->getTitle(),
            '{due_date}' => Yii::$app->formatter->asDate($this->due_date),
        ]));
    }


    /**
     * @return bool
     */
    public function hasForkQuantityProducts()
    {
        foreach ($this->products as $product) {
            if ($product->forkQuantityProducts) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return float|int
     */
    public function getProductDiscount()
    {
        $productDiscount = 0;
        foreach ($this->products as $product) {
            $productDiscount += $product->quote_discount_price;
        }
        return $productDiscount;
    }

    /**
     * @return float|int
     */
    public function getProductUnlockedOffset()
    {
        $offset = 0;
        foreach ($this->products as $product) {
            $offset += $product->quote_factor_price - $product->quote_total_price_unlocked;
        }
        return $offset;
    }

    /**
     * @return bool
     */
    public function hideTotals()
    {
        if ($this->quote_totals_format == 'hide-totals') {
            return true;
        }
        if ($this->hasForkQuantityProducts()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hideProductPrices()
    {
        if ($this->quote_totals_format == 'hide-product-prices') {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hideProductDiscounts()
    {
        return $this->quote_totals_format != 'show-product-discounts';
    }

    /**
     * @return bool
     */
    public function checkPriceMargin()
    {
        $threshold = 0.7;
        if ($this->quote_total_cost <= 0) {
            return true;
        }
        $price = $this->quote_retail_price - $this->quote_discount_price;
        $limit = $this->quote_total_cost / $threshold;
        if ($price <= $limit) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function checkProductsPriceMargins()
    {
        foreach ($this->products as $product) {
            if (!$product->checkPriceMargin()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return float|int
     */
    public function getArea()
    {
        $cacheKey = 'getArea';
        $area = $this->getCache($cacheKey);
        if ($area !== false) return $area;

        $area = 0;
        foreach ($this->products as $product) {
            $area += $product->getArea();
        }
        return $this->setCache($cacheKey, $area);
    }

    /**
     * @return float|int
     */
    public function getPerimeter()
    {
        $cacheKey = 'getPerimeter';
        $perimeter = $this->getCache($cacheKey);
        if ($perimeter !== false) return $perimeter;

        $perimeter = 0;
        foreach ($this->products as $product) {
            $perimeter += $product->getPerimeter();
        }
        return $this->setCache($cacheKey, $perimeter);
    }

    /**
     * @return bool
     */
    public function checkAccess()
    {
        if (Yii::$app->user->can('staff')) {
            return true;
        }
        return Yii::$app->user->can('_company_' . $this->company_id);
    }

    /**
     * @return string
     */
    public function getIcons()
    {
        $cacheKey = 'getIcons';
        $icons = $this->getCache($cacheKey);
        if ($icons !== false) return $icons;

        $icons = [];
        //$icons[] = $this->getUrgentIcon();
        //$icons[] = $this->getStaffRepIcon();
        //$icons[] = $this->getStaffCsrIcon();
        $icons[] = $this->getAccountIcon();
        //$icons[] = $this->getCompanyTradingAgeIcon();
        //$icons[] = $this->getPurchaseOrderIcon();
        $icons[] = $this->getNoteIcon();
        //$icons[] = $this->getTicketIcon();
        $icons[] = $this->getPowdercoatIcon();
        $icons[] = $this->getPrebuildIcon();
        //$icons[] = $this->getInstallationIcon();
        $icons[] = $this->getNotificationIcon();
        foreach ($icons as $k => $v) {
            if (!$v) unset($icons[$k]);
        }
        return $this->setCache($cacheKey, implode(' ', $icons));
    }

    /**
     * @return bool|string
     */
    public function getStaffRepIcon()
    {
        return $this->staffRep->getAvatar(16, [
            'title' => Yii::t('app', 'AM') . ': ' . $this->staffRep->label,
            'data-toggle' => 'tooltip',
        ]);
    }

    /**
     * @return bool|string
     */
    public function getStaffCsrIcon()
    {
        return $this->staffCsr->getAvatar(16, [
            'title' => Yii::t('app', 'CSR') . ': ' . $this->staffCsr->label,
            'data-toggle' => 'tooltip',
        ]);
    }

    /**
     * @return bool|string
     */
    public function getPurchaseOrderIcon()
    {
        if ($this->purchase_order || !$this->company || !$this->company->purchase_order_required) {
            return false;
        }
        return Helper::getIcon('purchase_order_required.png', ['title' => Yii::t('app', 'Purchase order required.')]);
    }

    /**
     * @return bool|string
     */
    public function getNoteIcon()
    {
        $icon = $this->getCache('getNoteIcon');
        if ($icon !== false) {
            return $icon;
        }
        $notes = [];
        if ($this->notes) {
            $notes[] = 'job';
        }
        if ($this->company->notes) {
            $notes[] = 'company';
        }
        foreach ($this->products as $product) {
            if ($product->notes) {
                $notes[] = 'product-' . $product->id;
            }
        }
        if ($notes) {
            $title = Yii::t('app', 'There are notes in: {notes}', [
                'notes' => implode(' | ', $notes),
            ]);
            $icon = Html::a(Helper::getIcon('note.png', ['title' => $title]), ['/job/preview-notes', 'id' => $this->id], [
                'class' => 'modal-remote',
            ]);
        }
        return $this->setCache('getNoteIcon', $icon ? $icon : null);
    }

    /**
     * @return bool|string
     */
    public function getAccountIcon()
    {
        if (!$this->account_term_id) {
            return false;
        }
        // PWO
        if ($this->account_term_id == AccountTerm::ACCOUNT_TERM_PWO) {
            // invoice_sent
            if ($this->invoice_sent) {
                if ($this->invoice_paid) {
                    $tip = Yii::t('app', 'PWO - Invoice Paid');
                    $code = 'invoice_paid';
                } //not paid
                else {
                    $tip = Yii::t('app', 'PWO - Invoice Unpaid');
                    $code = 'invoice_unpaid';
                }
            } // no invoice_sent
            else {
                $tip = Yii::t('app', 'PWO - Not Invoiced');
                $code = 'invoice_todo';
            }
            return Helper::getIcon($code . '.png', ['title' => $tip]);
        }
        // COD
        if ($this->account_term_id == AccountTerm::ACCOUNT_TERM_COD) {
            // invoice_sent
            if ($this->invoice_sent) {
                if ($this->invoice_paid) {
                    $tip = Yii::t('app', 'COD - Invoice Paid');
                    $code = 'invoice_paid';
                } //not paid
                else {
                    $tip = Yii::t('app', 'COD - Invoice Unpaid');
                    $code = 'invoice_unpaid';
                }
            } // no invoice_sent
            else {
                $tip = Yii::t('app', 'COD - Not Invoiced');
                $code = 'invoice_todo';
            }
            return Helper::getIcon($code . '.png', ['title' => $tip]);
        }
        return '';
        // not PWO/COD
        $tip = $this->accountTerm->name;
        $icon = Helper::getIcon('account_term_' . $this->account_term_id . '.png', ['title' => $tip]);
        return $icon;
    }

    /**
     * @return string
     */
    public function getCompanyTradingAgeIcon()
    {
        $date = strtotime($this->company->first_job_due_date);
        $age = 'new';
        $time = 'never';
        if ($date > strtotime('-1year')) {
            $age = 'upcoming';
            $time = 'under 12 months';
        }
        if ($date <= strtotime('-1year')) {
            $age = 'established';
            $time = 'over 12 months';
        }
        $humanDate = $this->company->first_job_due_date ? Yii::$app->formatter->asDate($this->company->first_job_due_date) : 'never';
        return Helper::getIcon('trading_age_' . $age . '.png', ['title' => $age . ' - ' . $time . ' (' . $humanDate . ')']);
    }

    /**
     * @return bool|string
     */
    public function getPowdercoatIcon()
    {
        $icon = $this->getCache('getPowdercoatIcon');
        if ($icon !== false) {
            return $icon;
        }
        foreach ($this->products as $product) {
            foreach ($product->items as $item) {
                $option = $item->getProductToOption(Option::OPTION_POWDERCOAT);
                if ($option && $option->getValueDecoded()) {
                    $icon = Helper::getIcon('powdercoat.png', ['title' => Yii::t('app', 'Powdercoat required')]);
                    break(2);
                }
            }
        }
        return $this->setCache('getPowdercoatIcon', $icon ? $icon : null);
    }

    /**
     * @return bool|string
     */
    public function getPrebuildIcon()
    {
        foreach ($this->products as $product) {
            if ($product->prebuild_required) {
                return Helper::getIcon('prebuild.png', ['title' => Yii::t('app', 'Prebuild required.')]);
            }
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function getNotificationIcon()
    {
        $icon = $this->getCache('getNotificationIcon');
        if ($icon !== false) {
            return $icon;
        }
        $notifications = [];
        if ($this->notifications) {
            $notifications[] = 'job';
        }
        foreach ($this->products as $product) {
            if ($product->notifications) {
                $notifications[] = 'product-' . $product->id;
            }
        }
        if ($notifications) {
            $title = Yii::t('app', 'There are notifications in: {notifications}', [
                'notifications' => implode(' | ', $notifications),
            ]);
            $icon = Html::a(Helper::getIcon('flag_red.png', ['title' => $title]), ['/job/preview-notifications', 'id' => $this->id], [
                'class' => 'modal-remote',
            ]);
        }
        return $this->setCache('getNotificationIcon', $icon ? $icon : null);
    }

    /**
     * @return string
     */
    public function getDateClass()
    {
        if ($this->despatch_date == date('Y-m-d')) {
            return 'despatch-today';
        }
        $now = time();
        $despatch = strtotime($this->despatch_date);
        $diff = abs(floor(($now - $despatch) / (60 * 60 * 24)));
        if ($despatch > $now) {
            $class = 'despatch-upcoming';
        } else {
            $class = 'despatch-overdue';
        }
        return $class . ' ' . $class . '-' . $diff;
    }

    /**
     * @return bool|float
     */
    public function getExcessiveDiscount()
    {
        $allowance = 1.2;
        $productDiscount = $this->getProductDiscount() * $this->quote_markup;
        //$productUnlockedOffset = $this->getProductUnlockedOffset() * $this->quote_markup * -1;
        $productUnlockedOffset = 0;
        $discount = round($this->quote_discount_price + $productDiscount + $productUnlockedOffset, 2);
        if ($discount > round($this->quote_maximum_discount_price * $allowance, 2)) {
            return $discount;
        }
        return false;
    }

    /**
     * @return array|string
     */
    public static function optsFreightMethod()
    {
        $jobFreightMethods = Yii::$app->settings->get('jobFreightMethods', 'app');
        $jobFreightMethods = $jobFreightMethods ? explode("\n", $jobFreightMethods) : [];
        return array_combine($jobFreightMethods, $jobFreightMethods);
    }

    /**
     *
     */
    public function updateFreightDays()
    {
        if (!in_array($this->status, ['job/draft', 'job/quote', 'job/productionPending'])) {
            return;
        }
        $freight_days = false;
        if ($this->freight_method == 'Client to Collect') {
            $freight_days = 0;
        } else {
            foreach ($this->shippingAddresses as $shippingAddress) {
                $postcodeTime = PostcodeTime::findOne(['postcode' => $shippingAddress->postcode]);
                if ($postcodeTime && $postcodeTime->lead_days > $freight_days) {
                    $freight_days = $postcodeTime->lead_days;
                }
            }
        }
        if ($freight_days === false) {
            $freight_days = 8;
        }
        if ($this->freight_days != $freight_days) {
            $this->freight_days = $freight_days;
            if (!$this->save(false)) {
                throw new Exception('cannot save job-' . $this->id . ': ' . Helper::getErrorString($this));
            }
        }
    }

    /**
     * @return bool
     */
    public function checkUnitCount()
    {
        foreach ($this->products as $product) {
            if (!$product->checkUnitCount()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function checkTotals()
    {
        $productTotal = 0;
        foreach ($this->products as $product) {
            $productTotal += $product->quote_factor_price - $product->quote_discount_price;
        }
        return abs($productTotal - $this->quote_wholesale_price) < 0.01;
    }

    /**
     *
     */
    public function fixUnitCount()
    {
        foreach ($this->products as $product) {
            if (!$product->checkUnitCount()) {
                $product->fixUnitCount();
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function getShippingStates()
    {
        $cacheKey = 'getShippingStates';
        $states = $this->getCache($cacheKey);
        if ($states !== false) return $states;

        $states = [];
        foreach ($this->addresses as $address) {
            if ($address->type != Address::TYPE_SHIPPING) continue;
            $states[$address->state] = $address->state;
        }
        return $this->setCache($cacheKey, implode(', ', $states));
    }

    /**
     * @return float
     */
    public function getReportTotal()
    {
        return $this->quote_total_price - $this->quote_freight_price - $this->quote_tax_price;
    }

    /**
     * @param array $check
     * @return array
     */
    public function getChangedAlertEmails($check = [])
    {
        $emails = [];
        $alertStatusList = Correction::getChangedAlertStatusList();
        $status = explode('/', $this->status)[1];

        if (isset($alertStatusList[$this->status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList[$this->status]);
        if (isset($alertStatusList['job-*/' . $status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList['job-*/' . $status]);

        if (in_array($status, ['production', 'despatch'])) {
            foreach ($this->products as $product) {
                $emails = ArrayHelper::merge($emails, $product->getChangedAlertEmails());
            }
        }

        $emails = array_unique($emails);
        return $emails;
    }

    /**
     * @return Package
     * @throws Exception
     */
    public function createPackage()
    {
        $package = new Package;
        $package->loadDefaultValues();
        $package->status = 'package/packing';
        if (!$package->save()) {
            throw new Exception('Package could not be created.');
        }
        $jobAddress = $this->billingAddress;
        foreach ($this->shippingAddresses as $shippingAddress) {
            $jobAddress = $shippingAddress;
            break;
        }
        $address = new Address();
        $address->model_name = $package->className();
        $address->model_id = $package->id;
        $address->type = Address::TYPE_SHIPPING;
        $address->name = $jobAddress->name;
        $address->street = $jobAddress->street;
        $address->postcode = $jobAddress->postcode;
        $address->city = $jobAddress->city;
        $address->state = $jobAddress->state;
        $address->country = $jobAddress->country;
        $address->contact = $jobAddress->contact;
        $address->phone = $jobAddress->phone;
        $address->instructions = $jobAddress->instructions;

        if (!$address->save()) {
            throw new Exception(Yii::t('app', 'Address could not be created for {package}.', [
                'package' => 'package-' . $package->id,
            ]));
        }
        if (!$package->save()) {
            throw new Exception(Yii::t('app', 'Package {package} could not be updated.', [
                'package' => 'package-' . $package->id,
            ]));
        }

        return $package;
    }


}
