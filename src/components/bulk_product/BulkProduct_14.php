<?php

namespace app\components\bulk_product;

use app\models\Component;
use yii\helpers\ArrayHelper;

class BulkProduct_14 extends BulkProduct
{
    const PRODUCT_TYPE_ID = 14;

    public static function getDoc()
    {
        return [
            'name' => 'Reframe DS101',
            'quantity' => 'Product Quantity',
            'unit_price' => 'Price the customer will pay per unit',
            'size' => 'WxH (in mm)',
            'front_skin.quantity' => 'Front Skin Quantity (this will be multiplied by Product Quantity)',
            'front_skin.substrate' => 'Front Skin Substrate (exact component code)',
            'front_skin.printer' => 'Front Skin Printer (PRINT or leave empty for blank)',
            'front_skin.label' => 'Front Skin Label (Plain, Reframe, AFI Branding or Octanorm)',
            'front_skin.artwork' => 'Front Skin Artwork Quantity',
            'back_skin.quantity' => 'Back Skin Quantity (this will be multiplied by Product Quantity)',
            'back_skin.substrate' => 'Back Skin Substrate (exact component code)',
            'back_skin.printer' => 'Back Skin Printer (PRINT or leave empty for blank)',
            'back_skin.label' => 'Back Skin Label (Plain, Reframe, AFI Branding or Octanorm)',
            'back_skin.artwork' => 'Back Skin Artwork Quantity',
            'liner.quantity' => 'Liner Quantity (this will be multiplied by Product Quantity)',
            'liner.substrate' => 'Liner Substrate (exact component code)',
            'frame.quantity' => 'Frame Quantity (this will be multiplied by Product Quantity)',
            'frame.extrusion' => 'Frame Extrusion (exact component code)',
            'frame.rig' => 'Frame Rig (exact component code)',
            'frame.powder_coat' => 'Frame Powder Coat (exact colour name)',
        ];
    }

    public static function getSample($job)
    {
        $sample = parent::getSample($job);
        $sample[0] = ArrayHelper::merge([
            'name' => 'My Reframe DS101',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1000x2000',
            'front_skin.quantity' => '1',
            'front_skin.substrate' => 'MATTEEX',
            'front_skin.printer' => 'PRINT',
            'front_skin.label' => 'Plain',
            'front_skin.artwork' => '1',
            'back_skin.quantity' => '1',
            'back_skin.substrate' => 'MATTEEX',
            'back_skin.printer' => 'PRINT',
            'back_skin.label' => 'Plain',
            'back_skin.artwork' => '1',
            'liner.quantity' => '1',
            'liner.substrate' => 'CP-BLOCKOUT',
            'frame.quantity' => '1',
            'frame.extrusion' => 'DS101',
            'frame.rig' => '',
            'frame.powder_coat' => '',
        ], $sample[0]);
        $sample[1] = ArrayHelper::merge([
            'name' => 'Another Reframe DS101',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1500x1500',
            'front_skin.quantity' => '1',
            'front_skin.substrate' => 'MATTEEX',
            'front_skin.printer' => 'PRINT',
            'front_skin.label' => 'Plain',
            'front_skin.artwork' => '1',
            'back_skin.quantity' => '1',
            'back_skin.substrate' => 'MATTEEX',
            'back_skin.printer' => 'PRINT',
            'back_skin.label' => 'Plain',
            'back_skin.artwork' => '1',
            'liner.quantity' => '1',
            'liner.substrate' => 'MATTEEX',
            'frame.quantity' => '1',
            'frame.extrusion' => 'DS101',
            'frame.rig' => 'RP008',
            'frame.powder_coat' => 'Satin Black',
        ], $sample[1]);
        return $sample;
    }

    public static function getMap()
    {
        return ArrayHelper::merge(parent::getMap(), [
            //'size_id' => 'ProductToOptions[new0][valueDecoded][value]',
            'width' => 'ProductToOptions[new0][valueDecoded][height]',
            'height' => 'ProductToOptions[new0][valueDecoded][width]',
            'front_skin.quantity' => 'Items[new0][quantity]',
            'front_skin.substrate' => 'ProductToOptions[new1005][valueDecoded]',
            'front_skin.printer' => 'ProductToOptions[new1006][valueDecoded]',
            'front_skin.label' => 'ProductToOptions[new1007][valueDecoded]',
            'front_skin.artwork' => 'ProductToOptions[new1008][quantity]',
            'back_skin.quantity' => 'Items[new1][quantity]',
            'back_skin.substrate' => 'ProductToOptions[new1010][valueDecoded]',
            'back_skin.printer' => 'ProductToOptions[new1011][valueDecoded]',
            'back_skin.label' => 'ProductToOptions[new1012][valueDecoded]',
            'back_skin.artwork' => 'ProductToOptions[new1013][quantity]',
            'liner.quantity' => 'Items[new2][quantity]',
            'liner.substrate' => 'ProductToOptions[new1015][valueDecoded]',
            'frame.quantity' => 'Items[new3][quantity]',
            'frame.extrusion' => 'ProductToOptions[new1002][valueDecoded]',
            'frame.rig' => 'ProductToOptions[new1003][valueDecoded][component]',
            'frame.powder_coat' => 'ProductToOptions[new1004][valueDecoded]',
        ]);
    }

    public static function getProductFormAttributes($row)
    {
        $mapped = parent::getProductFormAttributes($row);
        $size = explode('x', strtolower(trim($row['size'])));
        $mapped['ProductToOptions']['new0']['valueDecoded']['value'] = 2;
        $mapped['ProductToOptions']['new0']['valueDecoded']['width'] = trim($size[0]);
        $mapped['ProductToOptions']['new0']['valueDecoded']['height'] = isset($size[1]) ? trim($size[1]) : 0;
        $substrate = Component::findOne(['code' => $row['front_skin.substrate']]);
        if ($substrate) {
            $mapped['ProductToOptions']['new1005']['valueDecoded'] = $substrate->id;
        }
        $substrate = Component::findOne(['code' => $row['back_skin.substrate']]);
        if ($substrate) {
            $mapped['ProductToOptions']['new1010']['valueDecoded'] = $substrate->id;
        }
        $printer = Component::findOne(['code' => $row['front_skin.printer']]);
        if ($printer) {
            $mapped['ProductToOptions']['new1006']['valueDecoded'] = $printer->id;
        }
        $printer = Component::findOne(['code' => $row['back_skin.printer']]);
        if ($printer) {
            $mapped['ProductToOptions']['new1011']['valueDecoded'] = $printer->id;
        }
        $extrusion = Component::findOne(['code' => $row['frame.extrusion']]);
        if ($extrusion) {
            $mapped['ProductToOptions']['new1002']['valueDecoded'] = $extrusion->id;
        }
        if (!empty($row['frame.rig'])) {
            $rig = Component::findOne(['code' => $row['frame.rig']]);
            if ($rig) {
                $mapped['ProductToOptions']['new1003']['valueDecoded']['component'] = $rig->id;
            }
        }
        $mapped['ProductToOptions']['new1009']['valueDecoded']['value'] = 53;
        $mapped['ProductToOptions']['new1014']['valueDecoded']['value'] = 54;
        $mapped['ProductToOptions']['new1016']['valueDecoded']['value'] = 51;

        if (empty($mapped['ProductToOptions']['new1007']['valueDecoded'])) {
            $mapped['ProductToOptions']['new1007']['valueDecoded'] = 'Reframe';
        }
        if (empty($mapped['ProductToOptions']['new1012']['valueDecoded'])) {
            $mapped['ProductToOptions']['new1012']['valueDecoded'] = 'Reframe';
        }

        return $mapped;
    }
}