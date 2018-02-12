<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Address;
use app\models\Package;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class AddressPackageCreateForm
 * @package app\models\form
 *
 * @property \app\models\Package $package
 */
class AddressPackageCreateForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var
     */
    public $quantity = 1;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var int
     */
    public $print_labels;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['print_labels', 'quantity'], 'integer'],
            [['print_spool'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->print_spool = Yii::$app->user->identity->print_spool;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->dbData->beginTransaction();

        foreach ($this->ids as $id) {
            for ($i = 0; $i < $this->quantity; $i++) {
                $baseAddress = Address::findOne($id);
                //$job = Job::findOne($baseAddress->model_id);

                // create package
                $package = new Package();
                $package->loadDefaultValues();
                if (!$package->save()) {
                    $this->addError('package', Yii::t('app', 'Package could not be saved.'));
                    $transaction->rollBack();
                    return false;
                }

                // create address
                $address = new Address();
                $address->loadDefaultValues();
                $address->model_id = $package->id;
                $address->model_name = $package->className();
                $address->type = Address::TYPE_SHIPPING;
                $address->name = $baseAddress->name;
                $address->street = $baseAddress->street;
                $address->postcode = $baseAddress->postcode;
                $address->city = $baseAddress->city;
                $address->state = $baseAddress->state;
                $address->country = $baseAddress->country;
                $address->contact = $baseAddress->contact;
                $address->phone = $baseAddress->phone;
                $address->instructions = $baseAddress->instructions;
                if (!$address->save()) {
                    $this->addError('package', Yii::t('app', 'Address could not be saved.'));
                    $transaction->rollBack();
                    return false;
                }

                // print
                Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);
                if ($this->print_spool && $this->print_labels) {
                    PrintManager::printPackageLabel($this->print_spool, $package);
                }
            }
        }

        $transaction->commit();
        return true;
    }

}