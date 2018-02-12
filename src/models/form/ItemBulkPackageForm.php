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
 * Class ItemBulkPackageForm
 * @package app\models\form
 *
 */
class ItemBulkPackageForm extends Model
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
    public $package_id;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var
     */
    public $print;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids', 'package_id'], 'required'],
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
        if (isset($values['ItemBulkPackageForm'])) {
            foreach ($values['ItemBulkPackageForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ItemBulkPackageForm']);
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
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        $package_id = trim(str_replace('package-', '', $this->package_id));
        if ($package_id == 'new') {
            $package = $this->createPackage();
        } else {
            $package = Package::findOne($package_id);
        }
        if (!$package) {
            throw new Exception('Could not find package.');
        }

        $unitStatus = 'unit/packed';
        if ($package->pickup && $package->pickup->status == 'pickup/collected') {
            $unitStatus = 'unit/complete';
        }
        $ids = [];
        foreach (explode("\n", $this->ids) as $id) {
            $ids[] = trim(str_replace('item-', '', $id));
        }
        foreach ($ids as $id) {
            $item = Item::findOne($id);
            foreach ($item->units as $unit) {
                if (!$unit->package_id) {
                    $unit->package_id = $package->id;
                    $unit->status = explode('/', $unit->status)[0] . '/' . explode('/', $unitStatus)[1];
                    if (!$unit->save()) {
                        throw new Exception('Could not save unit-' . $unit->id . ' of item-' . $unit->item_id . ': ' . Helper::getErrorString($unit));
                    }
                }
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @return Package|bool
     * @throws Exception
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
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['package_label'] = Yii::t('app', 'Package Label');
        return $print;
    }

}