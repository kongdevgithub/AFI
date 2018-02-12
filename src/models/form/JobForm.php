<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\components\Helper;
use app\models\Address;
use app\models\Company;
use app\models\HubSpotDeal;
use app\models\Item;
use app\models\Job;
use app\models\Product;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\widgets\ActiveForm;

/**
 * Class JobForm
 * @package app\models\form
 *
 * @property \app\models\Job $job
 * @property \app\models\Address[] $addresses
 */
class JobForm extends Model
{

    /**
     * @var
     */
    public $items = [];

    /**
     * product id and quantity map
     * @var array
     */
    public $products = [];

    /**
     * @var array
     */
    public $productsMeta = [];

    /**
     * @var array
     */
    public $preserve_unit_prices = [];

    /**
     * @var Job
     */
    private $_job;

    /**
     * @var bool
     */
    public $copy_notes = true;
    /**
     * @var bool
     */
    public $copy_attachments = true;
    /**
     * @var bool
     */
    public $copy_addresses = true;

    /**
     * @var string
     */
    public $redo_reason;

    /**
     * @var Address[]
     */
    //private $_addresses;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Job', 'products', 'productsMeta', 'items', 'preserve_unit_prices',
                'copy_notes', 'copy_attachments'], 'safe'],
            // 'Addresses'
            //[['Addresses'], 'validateAddresses'],
            [['redo_reason'], 'required', 'when' => function ($model) {
                /** @var Job $model */
                return $model->scenario == 'redo';
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['JobForm'])) {
            foreach ($values['JobForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['JobForm']);
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['Job'];
        $scenarios['update'] = ['Job', 'products', 'items', 'preserve_unit_prices'];
        $scenarios['version'] = ['Job', 'products', 'items', 'preserve_unit_prices'];
        $scenarios['copy'] = ['Job', 'products', 'productsMeta', 'copy_notes', 'copy_attachments', 'items', 'preserve_unit_prices'];
        $scenarios['redo'] = ['Job', 'products', 'items', 'preserve_unit_prices', 'redo_reason'];
        return $scenarios;
    }


    /**
     * @param $attribute
     */
    //public function validateAddresses($attribute)
    //{
    //    $hasBilling = false;
    //    //$hasShipping = false;
    //    foreach ($this->$attribute as $address) {
    //        /** @var Address $address */
    //        if ($address->type == Address::TYPE_BILLING) {
    //            if ($hasBilling) {
    //                $this->addError($attribute, Yii::t('app', 'Job can only have one Billing Address'));
    //            }
    //            $hasBilling = true;
    //        }
    //        //if ($address->type == Address::TYPE_SHIPPING) {
    //        //    $hasShipping = true;
    //        //}
    //    }
    //    if (!$hasBilling) {
    //        $this->addError($attribute, Yii::t('app', 'Job requires a Billing Address'));
    //    }
    //    //if (!$hasShipping) {
    //    //    $this->addError($attribute, Yii::t('app', 'Job requires a Shipping Address'));
    //    //}
    //}

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->job->validate()) {
            $error = true;
        }
        //foreach ($this->addresses as $address) {
        //    if (!$address->validate()) {
        //        $error = true;
        //    }
        //}
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if ($this->job->isAttributeChanged('company_id', false)) {
            $company = $this->job->company_id ? Company::findOne($this->job->company_id) : false;
            if ($company) {
                if (!Yii::$app->user->can('_update_account_term')) {
                    $this->job->account_term_id = $company->account_term_id;
                }
                if (!Yii::$app->user->can('_update_price_structure')) {
                    $this->job->price_structure_id = $company->price_structure_id;
                }
            }
        }

        if (!$this->validate()) {
            return false;
        }

        $resetQuoteGenerated = false;
        if ($this->job->price_structure_id != $this->job->getOldAttribute('price_structure_id')
            || $this->job->quote_class != $this->job->getOldAttribute('quote_class')
        ) {
            $resetQuoteGenerated = true;
        }

        if (!$this->job->save()) {
            $this->addError('company_id', Helper::getErrorString($this->job));
            //$transaction->rollBack();
            return false;
        }
        if ($this->redo_reason) {
            $this->job->redo_reason = $this->redo_reason; // saved in eav
            $this->job->save(false);
        }
        $transaction = Yii::$app->dbData->beginTransaction(); // start transaction after job save because redo_reason is eav

        // set the vid
        if (!$this->job->vid) {
            $this->job->vid = $this->job->generateVid();
            $this->job->save(false);
        }

        // update quantities
        if ($this->scenario == 'update') {
            foreach ($this->products as $product_id => $quantity) {
                $product = Product::findOne($product_id);
                $product->preserve_unit_prices = !empty($this->preserve_unit_prices[$product_id]);
                $product->quantity = $quantity;
                $product->save(false);
                $product->resetQuoteGenerated();
                $resetQuoteGenerated = true;
            }
            foreach ($this->items as $item_id => $quantity) {
                $item = Item::findOne($item_id);
                if ($item->quantity != $quantity) {
                    $item->quantity = $quantity;
                    $item->save(false);
                }
                $item->product->resetQuoteGenerated();
                $resetQuoteGenerated = true;
            }
        }

        // copy/version/redo
        if (in_array($this->scenario, ['version', 'copy', 'redo'])) {
            $resetQuoteGenerated = true;

            $job_id = false;
            if ($this->scenario == 'version') {
                $job_id = $this->job->fork_version_job_id;
            }
            if ($this->scenario == 'copy') {
                $job_id = $this->job->copy_job_id;
            }
            if ($this->scenario == 'redo') {
                $job_id = $this->job->redo_job_id;
            }
            $modelCopy = $job_id ? Job::findOne($job_id) : false;
            if (!$modelCopy) {
                $transaction->rollBack();
                throw new Exception('could not find a Job');
            }

            $itemAttributes = [];
            foreach ($this->items as $item_id => $quantity) {
                $itemAttributes[$item_id]['quantity'] = $quantity;
            }
            foreach ($modelCopy->products as $_product) {
                $productAttributes = [
                    'Product' => [
                        'job_id' => $this->job->id,
                        'preserve_unit_prices' => !empty($this->preserve_unit_prices[$_product->id]) ? 1 : 0,
                    ],
                ];
                if (isset($this->products[$_product->id])) {
                    if ($this->products[$_product->id] < 1) continue; // don't copy when quantity<1
                    $productAttributes['Product']['quantity'] = $this->products[$_product->id];
                }
                $options = [
                    'copy_attachments' => true,
                    'copy_notes' => true,
                ];
                if (in_array($this->scenario, ['copy', 'redo'])) {
                    $options = [
                        'copy_attachments' => isset($this->productsMeta[$_product->id]['copy_attachments']) ? $this->productsMeta[$_product->id]['copy_attachments'] : false,
                        'copy_notes' => isset($this->productsMeta[$_product->id]['copy_notes']) ? $this->productsMeta[$_product->id]['copy_notes'] : false,
                    ];
                }
                $__product = $_product->copy($productAttributes, $itemAttributes, $options);
                $__product->resetQuoteGenerated();
            }
            $notes = $this->copy_notes ? $modelCopy->notes : [];
            foreach ($notes as $_note) {
                $_note->copy([
                    'Note' => [
                        'model_name' => $this->job->className(),
                        'model_id' => $this->job->id,
                    ],
                ]);
            }

            $attachments = $this->copy_attachments ? $modelCopy->attachments : [];
            foreach ($attachments as $_attachment) {
                $_attachment->copy([
                    'Attachment' => [
                        'model_name' => $this->job->className(),
                        'model_id' => $this->job->id,
                    ],
                ]);
            }
            if (in_array($this->scenario, ['version', 'redo'])) {
                foreach ($this->job->addresses as $address) {
                    $address->delete();
                }
                foreach ($modelCopy->addresses as $_address) {
                    $_address->copy([
                        'Address' => [
                            'model_name' => $this->job->className(),
                            'model_id' => $this->job->id,
                        ],
                    ]);
                }
            }
        }

        //if (!$this->saveAddresses()) {
        //    $transaction->rollBack();
        //    return false;
        //}
        $transaction->commit();

        // push the deal back to hubspot
        GearmanManager::runHubSpotPush(HubSpotDeal::className(), $this->job->id);

        // reset the quote
        if ($resetQuoteGenerated) {
            $this->job->resetQuoteGenerated(false);
        }

        return true;
    }

    /**
     * @return bool
     */
    //public function saveAddresses()
    //{
    //    $keep = [];
    //    foreach ($this->addresses as $address) {
    //        $address->model_name = $this->job->className();
    //        $address->model_id = $this->job->id;
    //        if (!$address->save(false)) {
    //            return false;
    //        }
    //        $keep[] = $address->id;
    //    }
    //    $query = Address::find()->andWhere(['model_name' => $this->job->className(), 'model_id' => $this->job->id]);
    //    if ($keep) {
    //        $query->andWhere(['not in', 'id', $keep]);
    //    }
    //    foreach ($query->all() as $item) {
    //        $item->delete();
    //    }
    //    return true;
    //}

    /**
     * @return mixed
     */
    public function getJob()
    {
        return $this->_job;
    }

    /**
     * @param $job
     */
    public function setJob($job)
    {
        if ($job instanceof Job) {
            $this->_job = $job;
        } else if (is_array($job)) {
            $this->_job->setAttributes($job);
        }
    }

    /**
     *
     */
    public function resetJob()
    {
        if (!in_array($this->scenario, ['copy', 'version', 'redo'])) {
            return;
        }

        // reset the new job
        $this->job->id = null;
        $this->job->vid = null;
        $this->job->status = 'job/draft';

        if ($this->scenario == 'copy') {
            //$this->job->company_id = null;
            //$this->job->contact_id = null;
            $this->job->account_term_id = null;
            $this->job->price_structure_id = null;
        }

        $this->job->fork_version_job_id = null;
        $this->job->copy_job_id = null;
        $this->job->redo_job_id = null;

        $this->job->production_date = null;
        $this->job->prebuild_date = null;
        $this->job->despatch_date = null;
        $this->job->due_date = null;
        $this->job->installation_date = null;

        $this->job->quote_at = null;
        $this->job->quote_lost_at = null;
        $this->job->complete_at = null;
        $this->job->packed_at = null;
        $this->job->despatch_at = null;
        $this->job->production_at = null;
        $this->job->production_pending_at = null;
        $this->job->feedback_at = null;
        $this->job->production_at = null;

        $this->job->freight_quote_requested_at = null;
        $this->job->freight_quote_provided_at = null;

        $this->job->invoice_sent = null;
        $this->job->invoice_reference = null;
        $this->job->invoice_amount = null;
        $this->job->invoice_paid = null;

        $this->job->redo_reason = null;

        $this->job->freight_quote_requested_at = null;
        $this->job->freight_quote_provided_at = null;

        $this->job->loadDefaultValues();

    }

    /**
     * @return Address[]
     */
    //public function getAddresses()
    //{
    //    if ($this->_addresses === null) {
    //        $this->_addresses = $this->job->isNewRecord ? [] : $this->job->addresses;
    //    }
    //    return $this->_addresses;
    //}

    /**
     * @param $id
     * @return Address|bool
     */
    //private function getAddress($id)
    //{
    //    $address = $id ? Address::findOne($id) : false;
    //    if (!$address) {
    //        $address = new Address();
    //        $address->loadDefaultValues();
    //    }
    //    return $address;
    //}

    /**
     * @param $addresses
     */
    //public function setAddresses($addresses)
    //{
    //    unset($addresses['__id__']); // remove the hidden "new Address" row
    //    $this->_addresses = [];
    //    foreach ($addresses as $id => $address) {
    //        if (is_array($address)) {
    //            $this->_addresses[$id] = $this->getAddress($id);
    //            $this->_addresses[$id]->setAttributes($address);
    //        } elseif ($address instanceof Address) {
    //            $this->_addresses[$id] = $address;
    //        }
    //    }
    //}

    /**
     * @param ActiveForm $form
     * @return mixed
     */
    public function errorSummary($form)
    {
        $errorLists = [];
        foreach ($this->getAllModels() as $id => $model) {
            $errorList = $form->errorSummary($model, [
                'header' => '<p>' . Yii::t('app', 'Please fix the following errors for') . ' <b>' . $id . '</b></p>',
            ]);
            $errorList = str_replace('<li></li>', '', $errorList); // remove the empty error
            $errorLists[] = $errorList;
        }
        return implode('', $errorLists);
    }

    /**
     * @return array
     */
    private function getAllModels()
    {
        $models = [
            'JobForm' => $this,
            'Job' => $this->job,
        ];
        //foreach ($this->addresses as $id => $address) {
        //    $models['Address.' . $id] = $this->addresses[$id];
        //}
        return $models;
    }
}