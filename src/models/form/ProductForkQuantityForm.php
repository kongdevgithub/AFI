<?php
namespace app\models\form;

use app\models\Item;
use app\models\Product;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use Yii;
use yii\base\Model;

/**
 * ProductForkQuantityForm
 */
class ProductForkQuantityForm extends Model
{

    /**
     * @var Product
     */
    public $product;

    /**
     * @var array
     */
    public $quantity;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['quantity'], 'safe'],
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

        foreach ($this->product->forkQuantityProducts as $_product) {
            $_product->delete();
        }

        if ($this->quantity) {
            foreach ($this->quantity as $quantity) {
                if (!$quantity) {
                    continue;
                }
                $product = $this->product->copy([
                    'Product' => [
                        'job_id' => $this->product->job_id,
                    ],
                ]);
                if (!$product) {
                    $transaction->rollBack();
                    return false;
                }
                $product->fork_quantity_product_id = $this->product->id;
                $product->quantity = $quantity;
                if (!$product->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
        }
        $transaction->commit();
        $this->product->job->resetQuoteGenerated();
        return true;
    }
}