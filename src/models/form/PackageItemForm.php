<?php

namespace app\models\form;

use app\components\Helper;
use app\components\LabelManager;
use app\components\PrintSpool;
use app\models\Address;
use app\models\Item;
use app\models\Job;
use app\models\Package;
use app\models\Unit;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class PackageItemForm
 * @package app\models\form
 *
 */
class PackageItemForm extends Model
{
    /**
     * @var Job
     */
    public $job;

    /**
     * @var
     */
    public $ids;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var
     */
    public $print;

    /**
     * @var array
     */
    public $messages = [];

    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var int
     */
    private $commandCount = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids'], 'required'],
            [['print_spool', 'print'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PackageItemForm'])) {
            foreach ($values['PackageItemForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PackageItemForm']);
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
        $this->messages = [];
        $transaction = Yii::$app->dbData->beginTransaction();


        $package_id = false;
        /** @var Job|bool $job */
        //$job = false;
        //$action = false;
        $statusToFind = 'despatch';
        $this->commands = explode("\n", trim($this->ids));
        $this->commandCount = -1;
        foreach ($this->commands as $id) {
            $this->commandCount++;

            // get the row id
            $id = trim($id);
            if (!$id) continue;

            // check for package
            if (substr($id, 0, 8) == 'package-') {
                //$action = false;
                $statusToFind = 'despatch';
                $package_id = substr($id, 8);
                if ($package_id == 'new') {
                    $package = $this->createPackage();
                    if (!$package) {
                        $transaction->rollBack();
                        return false;
                    }
                    //$action = 'new';
                    $package_id = $package->id;
                } elseif ($package_id == 'none') {
                    $statusToFind = 'packed';
                    //$action = 'remove';
                    $package_id = 0;
                }
                continue;
            }

            // check for item
            $item_id = false;
            if (substr($id, 0, 5) == 'item-') {
                $item_id = substr($id, 5);
            }
            $item = Item::findOne($item_id);
            if (!$item) {
                $this->addError('ids', Yii::t('app', 'Item {item} could not be found.', [
                    'item' => 'item-' . $item_id,
                ]));
                $transaction->rollBack();
                return false;
            }

            // assign the item to the package
            if (!$this->packageItem($package_id, $item_id, $statusToFind)) {
                $transaction->rollBack();
                return false;
            }
        }
        //Unit::afterBatchSave();

        $transaction->commit();
        return true;
    }

    /**
     * @return Package|bool
     */
    private function createPackage()
    {
        $package = $this->job->createPackage();
        if (!empty($this->print['package_label'])) {
            PrintSpool::spool($this->print_spool, LabelManager::getPackage($package));
        }
        return $package;
    }

    /**
     * @param $package_id
     * @param $item_id
     * @param $statusToFind
     * @return bool
     * @throws Exception
     */
    private function packageItem($package_id, $item_id, $statusToFind)
    {
        // find package
        $package = false;
        if ($package_id) {
            $package = Package::findOne($package_id);
            if (!$package) {
                $this->addError('ids', Yii::t('app', 'Package {package} could not be found.', [
                    'package' => 'package-' . $package_id,
                ]));
                return false;
            }
            // assign delivery_address the package
            $this->addressPackage($package);
        }

        // find item
        $item = Item::findOne($item_id);
        if (!$item) {
            $this->addError('ids', Yii::t('app', 'Item {item} could not be found.', [
                'item' => 'item-' . $item_id,
            ]));
            return false;
        }

        // check the company in the package
        if ($package) {
            if ($this->job->company_id != $item->product->job->company_id) {
                $this->addError('ids', Yii::t('app', 'Item company "{itemCompany}" does not match job company "{jobCompany}".', [
                    'jobCompany' => $this->job->company->name,
                    'itemCompany' => $item->product->job->company->name,
                ]));
                return false;
            }
        }

        // find a unit to update
        $units = Unit::find()
            ->notDeleted()
            ->andWhere(['item_id' => $item_id])
            ->andWhere('status like :status', [
                ':status' => '%/' . $statusToFind
            ])->all();

        if (!$units) {
            $this->addError('ids', Yii::t('app', 'Item {item} has no units in Status {status}.', [
                'item' => 'item-' . $item_id,
                'status' => $statusToFind,
            ]));
            return false;
            //$statusDisplay = $statusToFind;
            //if (is_array($statusDisplay)) {
            //    $statusDisplay = implode(',', $statusDisplay);
            //}
            //continue;
        }

        $packageUnit = ArrayHelper::map($units, 'package_id', 'id');

        // item is in multiple packages
        $packageRemoveId = false;
        if (count($packageUnit) > 1) {

            // in multiple packages mentioned which package to remove item from
            if (isset($this->commands[$this->commandCount - 2]) && ((substr($lastCommand = $this->commands[$this->commandCount - 2], 0, 8) == 'package-'))) {
                $validPackage = false;
                $packageRemoveId = trim(substr($lastCommand, 8));
                if ($packageRemoveId && is_numeric($packageRemoveId)) {
                    //item is present in the package
                    if (in_array($packageRemoveId, array_keys($packageUnit))) {
                        $validPackage = true;
                        $unit_id = $packageUnit[$packageRemoveId];
                        $unit = Unit::findOne($unit_id);
                        //units array just has one unit which should be removed
                        $units = [$unit];
                    }
                }
                if (!$validPackage) {
                    $this->addError('ids', Yii::t('app', 'Item {item} is not in Package {package}.', [
                        'item' => 'item-' . $item_id,
                        'package' => 'package-' . $packageRemoveId,
                    ]));
                    return false;
                }
            }

            // multiple packages but did not specify from which package to remove the item from
            if (!$packageRemoveId) {
                $packageList = [];
                foreach ($units as $unit) {
                    $_package = Package::findOne($unit->package_id);
                    if ($_package) {
                        $packageList[] = $_package->getLink();
                    } else {
                        $this->addError('ids', Yii::t('app', 'Package {package} not found for Unit {unit}.', [
                            'package' => 'package-' . $unit->package_id,
                            'unit' => 'unit-' . $unit->id,
                        ]));
                        return false;
                    }
                }
                $this->addError('ids', Yii::t('app', 'Item {item} is in multiple Packages {packages}.', [
                    'item' => 'item-' . $item_id,
                    'packages' => implode(', ', $packageList),
                ]));
                return false;
            }
        }

        /** @var $unit Unit */
        $unit = array_values($units)[0];
        //Unit::$batchSave = true;

        // update the unit attributes
        $unitQuantity = $unit->quantity;
        $unitPackageId = $unit->package_id;
        $unitStatus = $unit->status;
        $unit->quantity = 1;
        $unit->package_id = $package_id ? $package_id : null;
        $workflow = explode('/', $unitStatus)[0];
        if ($package) {
            if ($package->pickup && $package->pickup->status == 'pickup/collected') {
                $unit->status = $workflow . '/complete';
            } else {
                $unit->status = $workflow . '/packed';
            }
        } else {
            $unit->status = $workflow . '/despatch';
        }

        // create a new unit with the quantity remaining
        if ($unitQuantity > 1) {
            $unitCopy = new Unit;
            $unitCopy->item_id = $unit->item_id;
            $unitCopy->package_id = $unitPackageId;
            $unitCopy->quantity = $unitQuantity - 1;
            $unitCopy->status = $unitStatus;
            $unitCopy->initStatus();
            $unitCopy->mergeExistingUnits = false;
            if (!$unitCopy->save(false)) { // do not validate or status will trigger an error
                throw new Exception('cannot save unit-' . $unitCopy->id . ': ' . Helper::getErrorString($unitCopy));
            }
        }

        // merge existing similar units
        $unitMerges = Unit::find()
            ->notDeleted()
            ->andWhere([
                'item_id' => $unit->item_id,
                'package_id' => $unit->package_id,
                'status' => $unit->status,
            ])->all();
        foreach ($unitMerges as $unitMerge) {
            $unit->quantity += $unitMerge->quantity;
            $unitMerge->delete();
        }

        // try to save
        if (!$unit->save()) {
            $this->addError('ids', Yii::t('app', 'Unit {unit} could not be updated.', [
                'unit' => 'unit-' . $unit->id,
            ]));
            return false;
        }

        //if ($unit->package) {
        //    Y::session()->addFlash('success', Yii::t('app', 'Item {item} has been added to Package {package}.', [
        //        'item' => $unit->item->getLink(),
        //        'package' => $unit->package->getLink(),
        //    ]));
        //} else {
        //    Y::session()->addFlash('success', Yii::t('app', 'Item {item} has been removed from Package {package}.', [
        //        'item' => $unit->item->getLink(),
        //        'package' => 'package-' . $unitPackageId,
        //    ]));
        //}
        return true;

    }

    /**
     * @param Package|bool $package
     * @return bool
     */
    private function addressPackage($package)
    {
        if (!$package || $package->address) return true;

        $address = new Address();
        $address->model_name = $package->className();
        $address->model_id = $package->id;
        $address->type = Address::TYPE_SHIPPING;
        $address->name = $this->job->billingAddress->name;
        $address->street = $this->job->billingAddress->street;
        $address->postcode = $this->job->billingAddress->postcode;
        $address->city = $this->job->billingAddress->city;
        $address->state = $this->job->billingAddress->state;
        $address->country = $this->job->billingAddress->country;
        if (!$address->save()) {
            $this->addError('ids', Yii::t('app', 'Address could not be created for {package}.', [
                'package' => 'package-' . $package->id,
            ]));
            return false;
        }
        if (!$package->save()) {
            $this->addError('ids', Yii::t('app', 'Package {package} could not be updated.', [
                'package' => 'package-' . $package->id,
            ]));
            return false;
        }
        //if ($action == 'new') {
        //    Y::session()->addFlash('success', Yii::t('app', 'Package {package} has been created.', [
        //        'package' => $package->getLink(),
        //    ]));
        //} elseif ($action == 'remove') {
        //    Y::session()->addFlash('success', Yii::t('app', 'Package {package} has been unassigned.', [
        //        'package' => $package->getLink(),
        //    ]));
        //} else {
        //    Y::session()->addFlash('success', Yii::t('app', 'Package {package} has been updated.', [
        //        'package' => $package->getLink(),
        //    ]));
        //}
        return true;

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['ids'] = Yii::t('app', 'Packages and Items');
        return $attributeLabels;
    }


    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['package_label'] = Yii::t('app', 'Package Label');
        return $print;
    }
}