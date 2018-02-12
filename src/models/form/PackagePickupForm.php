<?php

namespace app\models\form;

use app\components\CopeFreight;
use app\components\MyFreight;
use app\components\PrintManager;
use app\models\Carrier;
use app\models\Job;
use app\models\Package;
use app\models\Pickup;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class PackagePickupForm
 * @package app\models\form
 */
class PackagePickupForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var
     */
    public $pickup_id;

    /**
     * @var
     */
    public $status;

    /**
     * @var int
     */
    public $carrier_id;

    /**
     * @var
     */
    public $carrier_ref;

    /**
     * @var
     */
    public $assign_each_package_a_new_pickup;

    /**
     * @var
     */
    public $send_email;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print;

    /**
     * @var
     */
    public $upload_my_freight;

    /**
     * @var
     */
    public $upload_cope_freight;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->print_spool = Yii::$app->user->identity->print_spool;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pickup_id'], 'required'],
            [['carrier_id'], 'integer'],
            [['carrier_ref', 'status'], 'string'],
            [['send_email', 'assign_each_package_a_new_pickup'], 'safe'],
            [['print_spool', 'print'], 'safe'],
            [['upload_my_freight', 'upload_cope_freight'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PackagePickupForm'])) {
            foreach ($values['PackagePickupForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PackagePickupForm']);
        }
        if (!empty($values['print'])) {
            $print = [];
            foreach ($values['print'] as $k => $v) {
                $print[$v] = $v;
            }
            $values['print'] = $print;
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var Pickup[] $newPickups */
        $newPickups = [];

        $transaction = Yii::$app->dbData->beginTransaction();

        // remove pickup
        if ($this->pickup_id == 'none') {
            $this->pickup_id = null;
        }

        // new pickup
        if ($this->pickup_id == 'new') {
            if (!$this->assign_each_package_a_new_pickup) {
                $pickup = $this->createPickup();
                if (!$pickup) {
                    $transaction->rollBack();
                    return false;
                }
                $this->pickup_id = $pickup->id;
                $newPickups[] = $pickup;
            }
        } else {
            $this->assign_each_package_a_new_pickup = false;
        }

        // save packages
        foreach ($this->ids as $id) {
            if ($this->assign_each_package_a_new_pickup) {
                $pickup = $this->createPickup();
                if (!$pickup) {
                    $transaction->rollBack();
                    return false;
                }
                $this->pickup_id = $pickup->id;
                $newPickups[] = $pickup;
            }
            $package = Package::findOne($id);
            $package->pickup_id = $this->pickup_id;
            if (!$package->save()) {
                $transaction->rollBack();
                return false;
            }
        }

        // set new pickup status
        if ($newPickups && $this->status) {
            foreach ($newPickups as $newPickup) {
                $newPickup->status = $this->status;
                if (!$newPickup->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
        }

        $transaction->commit();

        // print
        if ($this->print && $this->print_spool) {
            Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);
            if ($newPickups) {
                foreach ($newPickups as $newPickup) {
                    if (!empty($this->print['pickup_pdf'])) {
                        PrintManager::printPickupPdf($this->print_spool, $newPickup);
                    }
                }
            } elseif ($this->pickup_id) {
                $pickup = Pickup::findOne($this->pickup_id);
                if (!empty($this->print['pickup_pdf'])) {
                    PrintManager::printPickupPdf($this->print_spool, $pickup);
                }
            }
            foreach ($this->ids as $id) {
                if (empty($this->print['package_pdf']) && empty($this->print['package_label'])) {
                    continue;
                }
                $package = Package::findOne($id);
                if (!empty($this->print['package_pdf'])) {
                    PrintManager::printPackagePdf($this->print_spool, $package);
                }
                if (!empty($this->print['package_label'])) {
                    PrintManager::printPackageLabel($this->print_spool, $package);
                }
            }
        }

        // upload to MyFreight
        if ($this->upload_my_freight) {
            if ($newPickups) {
                foreach ($newPickups as $newPickup) {
                    $newPickup->refresh();
                    MyFreight::upload($newPickup);
                }
            } elseif ($this->pickup_id) {
                $pickup = Pickup::findOne($this->pickup_id);
                MyFreight::upload($pickup);
            }
        }

        // upload to Cope
        if ($this->upload_cope_freight) {
            if ($newPickups) {
                foreach ($newPickups as $newPickup) {
                    $newPickup->refresh();
                    CopeFreight::upload($newPickup);
                }
            } elseif ($this->pickup_id) {
                $pickup = Pickup::findOne($this->pickup_id);
                CopeFreight::upload($pickup);
            }
        }

        return true;
    }

    /**
     * @return Pickup|bool
     */
    private function createPickup()
    {
        $pickup = new Pickup();
        $pickup->loadDefaultValues();
        $pickup->carrier_id = $this->carrier_id;
        $pickup->carrier_ref = $this->carrier_ref;
        if (!$pickup->save()) {
            return false;
        }
        return $pickup;
    }

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
            'PackageDimensionsForm' => $this,
            //'Package' => $this->package,
        ];
        return $models;
    }

    /**
     * @return array
     */
    public function optsPickup()
    {
        $pickups = [];
        foreach ($this->getPickups() as $pickup) {
            $pickups[$pickup->id] = 'pickup-' . $pickup->id;
        }
        return ArrayHelper::merge([
            'none' => Yii::t('app', 'Unassign Pickup'),
            'new' => Yii::t('app', 'New Pickup'),
        ], ArrayHelper::map($this->getPickups(), 'id', 'id'));
    }

    /**
     * @return Job[]
     */
    public function getJobs()
    {
        $jobs = [];
        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            foreach ($package->units as $unit) {
                $job = $unit->item->product->job;
                $jobs[$job->id] = $job;
            }
        }
        return $jobs;
    }

    /**
     * @return Pickup[]
     */
    public function getPickups()
    {
        $pickups = [];
        foreach ($this->getJobs() as $job) {
            foreach ($job->products as $product) {
                foreach ($product->items as $item) {
                    foreach ($item->units as $unit) {
                        if ($unit->package && $unit->package->pickup) {
                            $pickup = $unit->package->pickup;
                            $pickups[$pickup->id] = $pickup;
                        }
                    }
                }
            }
        }
        return $pickups;
    }

    /**
     * @return array
     */
    public function optsCarrier()
    {
        return ArrayHelper::map(Carrier::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['pickup_id'] = Yii::t('app', 'Pickup');
        $attributeLabels['carrier_id'] = Yii::t('app', 'Carrier');
        return $attributeLabels;
    }


    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['pickup_pdf'] = Yii::t('app', 'Pickup PDF');
        $print['package_pdf'] = Yii::t('app', 'Package PDF');
        $print['package_label'] = Yii::t('app', 'Package Label');
        return $print;
    }

}