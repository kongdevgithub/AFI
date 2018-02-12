<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Item;
use app\models\ItemToMachine;
use app\models\Job;
use app\models\Machine;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ItemProgressForm
 * @package app\models\form
 *
 */
class ItemProgressForm extends Model
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
     * @var int
     */
    public $machine_id;

    /**
     * @var string
     */
    public $machine_details;

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
            [['print_spool', 'print', 'machine_id', 'machine_details'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['ItemProgressForm'])) {
            foreach ($values['ItemProgressForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ItemProgressForm']);
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
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if ($this->item_ids) {
            $status = null;
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($status == null) {
                    $status = $item->status;
                }
                if ($item->status != $status) {
                    $this->addError('item_ids', Yii::t('app', 'Cannot handle mixed statuses.'));
                    break;
                }
            }
        }
        return parent::beforeValidate();
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
                        if ($item->status == $this->old_status) {
                            $items[] = $item;
                        }
                    }
                }
            }
        }
        if ($this->item_ids) {
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($item) {
                    if ($item->status == $this->old_status) {
                        $items[] = $item;
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
        // save ItemToMachine
        if ($this->machine_id) {
            $machine = Machine::findOne($this->machine_id);
            $machineTypes = $item->getMachineTypes();
            if ($machine && $machineTypes) {
                $itemToMachine = $item->getItemToMachine($machine->machine_type_id);
                if (!$itemToMachine) {
                    $itemToMachine = new ItemToMachine();
                    $itemToMachine->loadDefaultValues();
                    $itemToMachine->item_id = $item->id;
                    $itemToMachine->machine_id = $this->machine_id;
                    $itemToMachine->details = $this->machine_details;
                    if (!$itemToMachine->save()) {
                        return false;
                    }
                }
            }
        }
        // save Item
        $item->status = $this->new_status;
        if (!$item->save(false)) {
            return false;
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
     * @return array
     */
    public function optsMachine()
    {
        $machineTypes = [];
        if ($this->job_id) {
            $job = Job::findOne($this->job_id);
            if ($job) {
                foreach ($job->products as $product) {
                    foreach ($product->items as $item) {
                        foreach ($item->getMachineTypes() as $machineType) {
                            $machineTypes[$machineType->id] = $machineType;
                        }
                    }
                }
            }
        }
        if ($this->item_ids) {
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($item) {
                    foreach ($item->getMachineTypes() as $machineType) {
                        $machineTypes[$machineType->id] = $machineType;
                    }
                }
            }
        }
        if (!$machineTypes) {
            return [];
        }
        return ArrayHelper::map(Machine::find()
            ->notDeleted()
            ->andWhere([
                'machine_type_id' => ArrayHelper::map($machineTypes, 'id', 'id'),
            ])
            ->orderBy(['name' => SORT_ASC])
            ->all(), 'id', 'name');
    }

    /**
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
                        return $item->status;
                    }
                }
            }
        }
        if ($this->item_ids) {
            foreach ($this->item_ids as $item_id) {
                $item = Item::findOne($item_id);
                if ($item) {
                    return $item->status;
                }
            }
        }
        return 'item/draft';
    }
}