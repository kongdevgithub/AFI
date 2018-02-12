<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Attachment;
use app\models\Item;
use app\models\ItemToMachine;
use app\models\Machine;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * Class ItemStatusForm
 * @package app\models\form
 *
 * @property \app\models\Item $item
 * @property \app\models\ItemToMachine $itemToMachine
 * @property \app\models\Attachment $artwork
 */
class ItemStatusForm extends Model
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
     * @var string
     */
    public $artwork_email_text;

    /**
     * @var Item
     */
    private $_item;

    /**
     * @var ItemToMachine
     */
    private $_itemToMachine;

    /**
     * @var Attachment
     */
    private $_artwork;

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
            [['Item'], 'required'],
            [['new_status', 'print_spool', 'print', 'artwork_email_text', 'ItemToMachine', 'Artwork'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['ItemStatusForm'])) {
            foreach ($values['ItemStatusForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ItemStatusForm']);
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
        if (!$this->item->validate()) {
            $error = true;
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
        $this->item->status = $this->new_status;

        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        // save job
        if ($this->item->send_email) {
            if ($this->artwork_email_text) {
                $this->item->product->job->artwork_email_text = $this->artwork_email_text;
                $this->item->product->job->save(false);
            }
        }

        // save item
        if (!$this->item->save()) {
            $this->addError('item_id', Yii::t('app', 'Item is invalid.'));
            $transaction->rollBack();
            return false;
        }

        // save itemToMachine
        if ($this->itemToMachine) {
            if (!$this->saveItemToMachine()) {
                $this->addError('item_to_machine_id', Yii::t('app', 'ItemToMachine is invalid.'));
                $transaction->rollBack();
                return false;
            }
        }

        // save artwork
        if (!$this->saveArtwork()) {
            $this->addError('upload', Yii::t('app', 'Artwork is invalid.'));
            $transaction->rollBack();
            return false;
        }

        $transaction->commit();

        // print
        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);
        if (!empty($this->print['item_label'])) {
            for ($i = 0; $i < $this->item->quantity * $this->item->product->quantity; $i++) {
                PrintManager::printItemLabel($this->print_spool, $this->item);
            }
        }
        if (!empty($this->print['item_artwork']) && $this->item->artwork) {
            PrintManager::printItemArtwork($this->print_spool, $this->item);
        }
        if (!empty($this->print['item_production'])) {
            PrintManager::printItemProduction($this->print_spool, $this->item);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function saveItemToMachine()
    {
        $status = explode('/', $this->item->status)[1];
        if (in_array($status, ['rip', 'production'])) {
            $this->itemToMachine->item_id = $this->item->id;
            if (!$this->itemToMachine->save()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function saveArtwork()
    {
        if ($this->artwork && $this->artwork->upload) {
            if (!$this->artwork->upload('artwork-' . md5(microtime()) . '.' . pathinfo($this->artwork->upload->name, PATHINFO_EXTENSION))) {
                return false;
            }
            if (!$this->artwork->save()) {
                return false;
            }
            $this->item->clearCache();
        }
        return true;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * @param $item
     */
    public function setItem($item)
    {
        if ($item instanceof Item) {
            $this->_item = $item;
        } else if (is_array($item)) {
            $this->item->setAttributes($item);
        }
    }

    /**
     * @return ItemToMachine
     */
    public function getItemToMachine()
    {
        if ($this->_itemToMachine === null) {
            $machineTypes = $this->item->getMachineTypes();
            if ($machineTypes) {
                $this->_itemToMachine = $this->item->getItemToMachine($machineTypes[0]->id);
                if (!$this->_itemToMachine) {
                    $this->_itemToMachine = new ItemToMachine();
                    $this->_itemToMachine->loadDefaultValues();
                }
            }
        }
        return $this->_itemToMachine;
    }

    /**
     * @param $itemToMachine
     */
    public function setItemToMachine($itemToMachine)
    {
        if ($itemToMachine instanceof ItemToMachine) {
            $this->_itemToMachine = $itemToMachine;
        } else if (is_array($itemToMachine) && $this->itemToMachine) {
            if (!empty($itemToMachine['machine_id'])) {
                $this->itemToMachine->setAttributes($itemToMachine);
            } else {
                $this->_itemToMachine = false;
            }
        }
    }

    /**
     * @return Attachment
     */
    public function getArtwork()
    {
        if (!$this->_artwork) {
            $this->_artwork = $this->item->artwork;
            if (!$this->_artwork) {
                $this->_artwork = new Attachment();
                $this->_artwork->model_name = $this->item->className() . '-Artwork';
                $this->_artwork->model_id = $this->item->id;
            }
        }
        return $this->_artwork;
    }

    /**
     *
     */
    public function setArtwork($artwork)
    {
        $this->artwork->upload = UploadedFile::getInstanceByName('Artwork[upload]');
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
            'ItemStatusForm' => $this,
            'Item' => $this->item,
        ];
        if ($this->itemToMachine) {
            $models['ItemToMachine'] = $this->itemToMachine;
        }
        if ($this->artwork) {
            $models['Artwork'] = $this->artwork;
        }
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
        if ($this->item->artwork) {
            $print['item_artwork'] = Yii::t('app', 'Item Artwork');
        }
        return $print;
    }

    /**
     * @return array
     */
    public function optsMachine()
    {
        return ArrayHelper::map(Machine::find()
            ->notDeleted()
            ->andWhere([
                'machine_type_id' => ArrayHelper::map($this->item->getMachineTypes(), 'id', 'id'),
            ])
            ->all(), 'id', 'name');
    }
}