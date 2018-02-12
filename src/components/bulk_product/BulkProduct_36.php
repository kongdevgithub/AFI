<?php

namespace app\components\bulk_product;

use app\models\Component;
use yii\helpers\ArrayHelper;

class BulkProduct_36 extends BulkProduct
{

    const PRODUCT_TYPE_ID = 36;

    public static function getDoc()
    {
        return [
            'name' => 'Reframe Skin',
            'quantity' => 'Product Quantity',
            'unit_price' => 'Price the customer will pay per unit',
            'size' => 'WxH (in mm)',
            'skin.quantity' => 'Skin Quantity (this will be multiplied by Product Quantity)',
            'skin.substrate' => 'Skin Substrate (exact component code)',
            'skin.printer' => 'Skin Printer (PRINT or leave empty for blank)',
            'skin.label' => 'Skin Label (Plain, Reframe, AFI Branding or Octanorm)',
            'skin.artwork' => 'Skin Artwork Quantity',
        ];
    }

    public static function getSample($job)
    {
        $sample = parent::getSample($job);
        $sample[0] = ArrayHelper::merge([
            'name' => 'My Reframe Skin',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1000x2000',
            'skin.quantity' => '1',
            'skin.substrate' => 'MATTEEX',
            'skin.printer' => 'PRINT',
            'skin.label' => 'Plain',
            'skin.artwork' => '1',
        ], $sample[0]);
        $sample[1] = ArrayHelper::merge([
            'name' => 'Another Reframe Skin',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1500x1500',
            'skin.quantity' => '1',
            'skin.substrate' => 'BLACKOUT',
            'skin.printer' => 'PRINT',
            'skin.label' => 'ReFrame',
            'skin.artwork' => '1',
        ], $sample[1]);
        return $sample;
    }

    public static function getMap()
    {
        return ArrayHelper::merge(parent::getMap(), [
            //'size_id' => 'ProductToOptions[new0][valueDecoded][value]',
            'width' => 'ProductToOptions[new0][valueDecoded][height]',
            'height' => 'ProductToOptions[new0][valueDecoded][width]',
            'skin.quantity' => 'Items[new0][quantity]',
            'skin.substrate' => 'ProductToOptions[new1001][valueDecoded]',
            'skin.printer' => 'ProductToOptions[new1002][valueDecoded]',
            'skin.label' => 'ProductToOptions[new1003][valueDecoded]',
            'skin.artwork' => 'ProductToOptions[new1004][quantity]',
        ]);
    }

    public static function getProductFormAttributes($row)
    {
        $mapped = parent::getProductFormAttributes($row);
        $size = explode('x', strtolower(trim($row['size'])));
        $mapped['ProductToOptions']['new0']['valueDecoded']['value'] = 2;
        $mapped['ProductToOptions']['new0']['valueDecoded']['width'] = trim($size[0]);
        $mapped['ProductToOptions']['new0']['valueDecoded']['height'] = isset($size[1]) ? trim($size[1]) : 0;
        $substrate = Component::findOne(['code' => $row['skin.substrate']]);
        if ($substrate) {
            $mapped['ProductToOptions']['new1001']['valueDecoded'] = $substrate->id;
        }
        $printer = Component::findOne(['code' => $row['skin.printer']]);
        if ($printer) {
            $mapped['ProductToOptions']['new1002']['valueDecoded'] = $printer->id;
        }
        if (empty($mapped['ProductToOptions']['new1003']['valueDecoded'])) {
            $mapped['ProductToOptions']['new1003']['valueDecoded'] = 'Reframe';
        }
        return $mapped;
    }


}