<?php

namespace app\models\form;

use app\components\Helper;
use app\models\Package;
use app\models\Pickup;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class PickupPackageForm
 * @package app\models\form
 */
class PickupPackageForm extends Model
{

    /**
     * @var
     */
    public $package_ids;

    /**
     * @var Pickup
     */
    public $pickup;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['package_ids'], 'required'],
        ];
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

        // for each of the packages
        foreach (explode("\n", trim($this->package_ids)) as $package_id) {
            $package_id = trim($package_id);
            $package_id = str_replace('package-', '', $package_id);

            // find package
            $package = Package::findOne($package_id);
            if (!$package) {
                $this->addError('package_ids', Yii::t('app', 'Cannot find {package}.', [
                    'package' => 'package-' . $package_id,
                ]));
                $transaction->rollBack();
                return false;
            }

            // check if it's already assigned
            if ($package->pickup_id && $package->pickup_id != $this->pickup->id) {
                $this->addError('package_ids', Yii::t('app', 'Package {package} is already assigned to {pickup}.', [
                    'package' => 'package-' . $package_id,
                    'pickup' => 'pickup-' . $package->pickup_id,
                ]));
                $transaction->rollBack();
                return false;
            }

            // update package
            $package->pickup_id = $this->pickup->id;
            if (!$package->save()) {
                $this->addError('package_ids', Yii::t('app', 'Cannot save {package}: {error}', [
                    'package' => 'package-' . $package->id,
                    'error' => Helper::getErrorString($package),
                ]));
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

}