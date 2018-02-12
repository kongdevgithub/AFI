<?php

namespace app\models\form;

use app\components\Helper;
use app\models\Item;
use app\models\Option;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class ItemSplitForm
 * @package app\models\form
 *
 * @property Item $Item
 */
class ItemSplitForm extends Model
{
    /**
     * @var Item
     */
    public $item;

    /**
     * @var int
     */
    public $unit_count;

    /**
     * @var int
     */
    public $item_count;

    /**
     * @var int
     */
    public $assigned_units = 0;

    /**
     * @var array
     */
    public $quantities;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['item_count', 'quantities'], 'required'],
            [['item_count'], 'validateQuantities'],
        ];
    }


    /**
     * @param $attribute
     */
    public function validateQuantities($attribute)
    {
        $total = 0;
        $quantities = $this->quantities;
        unset($quantities[0]);
        if (count($quantities) < 2) {
            $this->addError($attribute, Yii::t('app', 'Item Count must be more than {number}.', [
                'number' => 2,
            ]));
            return;
        }
        foreach ($quantities as $quantity) {
            $total += $quantity;
        }
        if ($total != $this->item->quantity * $this->item->product->quantity) {
            $this->addError($attribute, Yii::t('app', 'Total must be exactly {total}.', [
                'total' => $this->item->quantity * $this->item->product->quantity,
            ]));
        }
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

        $quantities = $this->quantities;
        unset($quantities[0]);

        $transaction = Yii::$app->dbData->beginTransaction();

        $product = $this->item->product;

        // move product quantity into item
        if ($product->quantity > 1) {
            $productQuantity = $product->quantity;
            $product->quantity = 1;
            $product->save(false);
            if (!$product->save(false)) {
                $transaction->rollBack();
                throw new Exception('Cannot save product-' . $product->id . ': ' . Helper::getErrorString($product));
            }
            foreach ($product->items as $_item) {
                $_item->quantity = $_item->quantity * $productQuantity;
                if (!$_item->save(false)) {
                    $transaction->rollBack();
                    throw new Exception('Cannot save item-' . $_item->id . ': ' . Helper::getErrorString($_item));
                }
            }
        }

        // update the main item
        $this->item->quantity = array_shift($quantities);
        if (!$this->item->save(false)) {
            $transaction->rollBack();
            throw new Exception('Cannot save item-' . $this->item->id . ': ' . Helper::getErrorString($this->item));
        }
        $this->item->fixUnitCount();

        // create new items
        foreach ($quantities as $quantity) {
            $_item = $this->item->copy([
                'Item' => [
                    'status' => $this->item->status,
                    'quantity' => $quantity,
                    'split_id' => $this->item->id,
                ],
            ]);
            $productToOption = $_item->getProductToOption(Option::OPTION_ARTWORK);
            if ($productToOption) {
                $productToOption->delete();
            }
        }

        $transaction->commit();

        // reset quote
        $this->item->product->refresh();
        $this->item->product->resetQuoteGenerated();
        $this->item->product->job->resetQuoteGenerated(false);

        return true;
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
            'ItemSplitForm' => $this,
            'Item' => $this->item,
        ];
        return $models;
    }
}