<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\EmailManager;
use app\modules\goldoc\components\AfiExportHelper;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Venue;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class GoldocExportController
 * @package app\commands
 */
class GoldocExportController extends Controller
{

    /**
     * @return int
     * @throws \yii\base\Exception
     */
    public function actionAfi()
    {
        $this->stdout("Exporting AFI Products\n");

        //$csv = [];
        $venues = Venue::find()
            //->andWhere(['code' => 'UAC'])
            ->all();
        $count = count($venues);
        foreach ($venues as $k => $venue) {
            $this->stdout(CommandStats::stats($k + 1, $count) . $venue->code . ' - ');
            /** @var Product[] $products */
            $products = $venue->getProducts()
                ->andWhere(['>', 'quantity', 0])
                ->andWhere(['status' => 'goldoc-product/production'])
                ->andWhere(['supplier_id' => 1])
                ->andWhere(['or', ['supplier_reference' => null], ['supplier_reference' => '']])
                ->all();
            if ($products) {
                foreach ($products as $product) {
                    AfiExportHelper::addProductToJob($product);
                    $this->stdout('gd-product-' . $product->id . ' afi-product-' . $product->supplier_reference . ' - ');
                    //$csv[] = $this->getCsvRow($product);
                }
            }

            $this->stdout('done' . "\n");

        }

        // send email
        //EmailManager::sendGoldocAfiExport($csv);

        return self::EXIT_CODE_NORMAL;
    }


    /**
     * @return int
     */
    public function actionAdg()
    {

        $this->stdout("Exporting ADG Products\n");

        $products = Product::find()
            ->notDeleted()
            ->andWhere(['status' => ['goldoc-product/production']])
            ->andWhere(['supplier_id' => 2])
            ->andWhere(['or', ['supplier_reference' => null], ['supplier_reference' => '']])
            ->all();
        if (!$products) {
            $this->stdout('no products' . "\n");
        }
        $count = count($products);

        $csv = [];
        foreach ($products as $k => $product) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $csv[] = $this->getCsvRow($product);
            $product->status = 'goldoc-product/production';
            $product->supplier_reference = date('Y-m-d');
            $product->save(false);
            $this->stdout("\n");
        }
        EmailManager::sendGoldocAdgExport($csv);

        $this->stdout('exported products' . "\n");

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getCsvRow($product)
    {
        return [
            'product.id' => $product->id,
            'product.name' => $product->name,
            'product.quantity' => $product->quantity,
            'product.details' => $product->details,
            'product.loc' => $product->loc,
            'product.width' => $product->width,
            'product.height' => $product->height,
            'product.depth' => $product->depth,
            'product.comments' => $product->comments,
            'product.product_price' => $product->product_price,
            'product.status' => $product->status,
            'product.venue.id' => $product->venue_id,
            'product.venue.code' => $product->venue ? $product->venue->code : '',
            'product.venue.name' => $product->venue ? $product->venue->name : '',
            'product.item.id' => $product->item_id,
            'product.item.code' => $product->item ? $product->item->code : '',
            'product.item.name' => $product->item ? $product->item->name : '',
            'product.colour.id' => $product->colour_id,
            'product.colour.code' => $product->colour ? $product->colour->code : '',
            'product.colour.name' => $product->colour ? $product->colour->name : '',
            'product.design.id' => $product->design_id,
            'product.design.code' => $product->design ? $product->design->code : '',
            'product.design.name' => $product->design ? $product->design->name : '',
            'product.substrate.id' => $product->substrate_id,
            'product.substrate.code' => $product->substrate ? $product->substrate->code : '',
            'product.substrate.name' => $product->substrate ? $product->substrate->name : '',
            'product.supplier.id' => $product->supplier_id,
            'product.supplier.code' => $product->supplier ? $product->supplier->code : '',
            'product.supplier.name' => $product->supplier ? $product->supplier->name : '',
            'product.sponsor.id' => $product->sponsor_id,
            'product.sponsor.code' => $product->sponsor ? $product->sponsor->code : '',
            'product.sponsor.name' => $product->sponsor ? $product->sponsor->name : '',
        ];
    }

    public function actionFixAdg()
    {
        $this->stdout("Fixing ADG Products\n");

        $products = Product::find()
            ->notDeleted()
            ->andWhere(['supplier_id' => 2])
            ->andWhere(['status' => ['goldoc-product/production', 'goldoc-product/productionPending']])
            ->andWhere(['not', ['supplier_reference' => '']])
            ->andWhere(['not', ['supplier_reference' => null]])
            ->andWhere(['not', ['like', 'supplier_reference', '2017-']])
            ->all();
        if (!$products) {
            $this->stdout('no products' . "\n");
        }
        $count = count($products);

        $csv = [];
        foreach ($products as $k => $product) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($product->id . ' ' . $product->supplier_reference . ' - ');

            // delete AFI product
            $afiProduct = \app\models\Product::findOne($product->supplier_reference);
            $afiProduct->delete();

            // unset goldoc product
            $product->supplier_reference = null;
            $product->save();

            $this->stdout(' - done' . "\n");
        }
        EmailManager::sendGoldocAdgExport($csv);

        $this->stdout('exported products' . "\n");

        return self::EXIT_CODE_NORMAL;
    }

}