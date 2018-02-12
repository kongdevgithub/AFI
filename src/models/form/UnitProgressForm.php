<?php

namespace app\models\form;

use app\components\Helper;
use app\components\PrintManager;
use app\models\Item;
use app\models\Job;
use app\models\Unit;
use Yii;
use yii\base\Model;

/**
 * Class UnitProgressForm
 * @package app\models\form
 *
 */
class UnitProgressForm extends Model
{

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print;

    /**
     * @var int
     */
    public $job_id;

    /**
     * @var int[]
     */
    public $item_ids;

    /**
     * @var string
     */
    public $old_status;

    /**
     * @var string
     */
    public $new_status;

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
            [['new_status'], 'required'],
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
        if (isset($values['UnitProgressForm'])) {
            foreach ($values['UnitProgressForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['UnitProgressForm']);
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

        $transaction = Yii::$app->dbData->beginTransaction();

        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);

        foreach ($this->getItems() as $item) {
            if (!$this->processItem($item)) {
                $transaction->rollBack();
                return false;
            }
            $this->printItem($item);
        }

        $transaction->commit();
        return true;
    }


    /**
     * @return Item[]
     */
    public function getItems()
    {
        $items = [];
        if ($this->job_id) {
            $job = Job::findOne($this->job_id);
            if ($job) {
                foreach ($job->products as $product) {
                    foreach ($product->items as $item) {
                        foreach ($item->units as $unit) {
                            if ($unit->status == $this->old_status) {
                                $items[] = $item;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($this->item_ids) {
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($item) {
                    foreach ($item->units as $unit) {
                        if ($unit->status == $this->old_status) {
                            $items[] = $item;
                            break;
                        }
                    }
                }
            }
        }
        return $items;
    }

    /**
     * @param Item $item
     * @return bool
     */
    protected function processItem($item)
    {
        foreach ($item->units as $unit) {
            if ($unit->status != $this->old_status) {
                continue;
            }
            if (!$this->processUnit($unit)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Item $item
     */
    protected function printItem($item)
    {
        if (!empty($this->print['item_label'])) {
            for ($i = 0; $i < $item->quantity * $item->product->quantity; $i++) {
                PrintManager::printItemLabel($this->print_spool, $item);
            }
        }
        if (!empty($this->print['item_artwork']) && $item->artwork) {
            PrintManager::printItemArtwork($this->print_spool, $item);
        }
        if (!empty($this->print['item_production'])) {
            PrintManager::printItemProduction($this->print_spool, $item);
        }
    }

    /**
     * @param Unit $unit
     * @return bool
     */
    protected function processUnit($unit)
    {
        $unit->status = $this->new_status;
        $existingUnit = Unit::find()
            ->notDeleted()
            ->andWhere([
                'item_id' => $unit->item_id,
                'package_id' => $unit->package_id,
                'status' => $unit->status,
            ])
            ->one();
        if ($existingUnit) {
            $existingUnit->quantity += $unit->quantity;
            if (!$existingUnit->save(false)) {
                $this->addError('new_status', 'Cannot save unit-' . $existingUnit->id . ': ' . Helper::getErrorString($existingUnit));
                return false;
            }
            $unit->delete();
        } else {
            if (!$unit->save(false)) {
                $this->addError('new_status', 'Cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
                return false;
            }
        }
        return true;
    }


    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['item_label'] = Yii::t('app', 'Item Label');
        $print['item_production'] = Yii::t('app', 'Item Production');
        $print['item_artwork'] = Yii::t('app', 'Item Artwork');
        return $print;
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatus($status)
    {
        if ($status) {
            return $status;
        }
        if ($this->job_id) {
            $job = Job::findOne($this->job_id);
            if ($job) {
                foreach ($job->products as $product) {
                    foreach ($product->items as $item) {
                        if (!$item->quantity) continue;
                        foreach ($item->units as $unit) {
                            return $unit->status;
                        }
                    }
                }
            }
        }
        if ($this->item_ids) {
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($item) {
                    foreach ($item->units as $unit) {
                        return $unit->status;
                    }
                }
            }
        }
        return 'unit/draft';
    }
}