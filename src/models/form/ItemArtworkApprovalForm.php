<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\models\HubSpotDeal;
use app\models\Item;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class ItemArtworkApprovalForm
 * @package app\models\form
 *
 * @property \app\models\Item $item
 */
class ItemArtworkApprovalForm extends Model
{

    /**
     * @var string
     */
    public $full_name;

    /**
     * @var string
     */
    public $details;

    /**
     * @var Item
     */
    private $_item;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['full_name', 'details'], 'required'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        //if (!$this->job->validate()) {
        //    $error = true;
        //}
        if ($this->item->status != 'item-print/approval') {
            $this->addError('full_name', Yii::t('app', 'Item is not in the correct status for approval.'));
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
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->dbData->beginTransaction();

        $this->item->change_requested_by = $this->full_name;
        $this->item->change_request_details = $this->details;
        $this->item->status = 'item-print/change';

        if (!$this->item->save(false)) {
            $transaction->rollBack();
            return false;
        }

        $transaction->commit();

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
            $this->_item->setAttributes($item);
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
            'ItemArtworkApprovalForm' => $this,
            'Item' => $this->item,
        ];
        return $models;
    }
}