<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Address;
use app\models\Package;
use app\models\Unit;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class UnitStatusForm
 * @package app\models\form
 *
 * @property \app\models\Unit $unit
 * @property \app\models\Package $package
 * @property \app\models\Address $address
 */
class UnitStatusForm extends Model
{

    /**
     * @var
     */
    public $old_status;

    /**
     * @var
     */
    public $new_status;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print;

    /**
     * @var Unit
     */
    private $_unit;

    /**
     * @var Package
     */
    private $_package;

    /**
     * @var Address
     */
    private $_address;

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
            [['Unit'], 'required'],
            [['Address', 'Package', 'new_status', 'print_spool', 'print'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['UnitStatusForm'])) {
            foreach ($values['UnitStatusForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['UnitStatusForm']);
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
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->unit->validate()) {
            $error = true;
        }
        if ($this->unit->quantity > $this->unit->getOldAttribute('quantity')) {
            $this->unit->addError('quantity', Yii::t('app', 'You cannot progress this many units.'));
            $error = true;
        }
        $status = explode('/', $this->unit->status)[1];
        if (in_array($status, ['packed', 'collected'])) {
            if ($this->unit->package_id == 'new') {
                if (!$this->address->validate()) {
                    $error = true;
                }
            }
            //if (!$this->package->validate()) {
            //    $error = true;
            //}
        }
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->unit->status = $this->new_status;

        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();
        $status = explode('/', $this->unit->status)[1];
        $quantity = $this->unit->quantity;

        // save package
        if (in_array($status, ['packed', 'collected']) && !$this->unit->package_id) {
            if (!$this->package->save()) {
                $this->addError('package_id', Yii::t('app', 'Package is invalid.'));
                $transaction->rollBack();
                return false;
            }
            // save address
            $this->address->model_name = $this->package->className();
            $this->address->model_id = $this->package->id;
            $this->address->type = Address::TYPE_SHIPPING;
            if (!$this->address->save()) {
                $this->addError('package_id', Yii::t('app', 'Package address is invalid.'));
                $transaction->rollBack();
                return false;
            }
            // set unit package
            $this->unit->package_id = $this->package->id;
        }
        // save unit
        if (!$this->unit->save()) {
            $this->addError('unit_id', Yii::t('app', 'Unit is invalid.'));
            $transaction->rollBack();
            return false;
        }

        // print
        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);
        if (!empty($this->print['item_production'])) {
            PrintManager::printItemProduction($this->print_spool, $this->unit->item);
        }
        if (!empty($this->print['package_label']) && $this->unit->package) {
            PrintManager::printPackageLabel($this->print_spool, $this->unit->package);
        }
        if (!empty($this->print['item_label'])) {
            for ($i = 0; $i < $quantity; $i++) {
                PrintManager::printItemLabel($this->print_spool, $this->unit->item);
            }
        }
        if (!empty($this->print['item_artwork']) && $this->unit->item->artwork) {
            for ($i = 0; $i < $quantity; $i++) {
                PrintManager::printItemArtwork($this->print_spool, $this->unit->item);
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->_unit;
    }

    /**
     * @param $unit
     */
    public function setUnit($unit)
    {
        if ($unit instanceof Unit) {
            $this->_unit = $unit;
        } else if (is_array($unit)) {
            $this->unit->setAttributes($unit);
        }
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        $job = $this->unit->item->product->job;
        if ($this->_package === null) {
            if ($this->unit->isNewRecord || !$this->unit->package) {
                $this->_package = new Package();
                $this->_package->loadDefaultValues();
            } else {
                $this->_package = $this->unit->package;
            }
        }
        return $this->_package;
    }

    /**
     * @param $package
     */
    public function setPackage($package)
    {
        if ($package instanceof Package) {
            $this->_package = $package;
        } else if (is_array($package)) {
            $this->package->setAttributes($package);
        }
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        if ($this->_address === null) {
            if ($this->package->isNewRecord) {
                $this->_address = new Address();
                $this->_address->loadDefaultValues();
            } else {
                $this->_address = $this->package->address;
            }
        }
        return $this->_address;
    }

    /**
     * @param Address|array $address
     */
    public function setAddress($address)
    {
        if (is_array($address)) {
            $this->address->setAttributes($address);
        } elseif ($address instanceof Address) {
            $this->_address = $address;
        }
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
            'UnitStatusForm' => $this,
            'Unit' => $this->unit,
            'Package' => $this->package,
            'Address' => $this->address,
        ];
        return $models;
    }

    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['item_label'] = Yii::t('app', 'Item Label');
        $print['item_production'] = Yii::t('app', 'Item Production');
        if ($this->unit->item->artwork) {
            $print['item_artwork'] = Yii::t('app', 'Item Artwork');
        }
        if ($this->unit->package) {
            $print['package_label'] = Yii::t('app', 'Package Label');
        }
        return $print;
    }

}