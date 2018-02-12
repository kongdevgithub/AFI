<?php

namespace app\components\bulk_product;

use app\models\Component;
use yii\helpers\ArrayHelper;

class BulkProduct_18 extends BulkProduct
{
    const PRODUCT_TYPE_ID = 18;

    public static function getDoc()
    {
        return [
            'name' => 'Reframe SS101',
            'quantity' => 'Product Quantity',
            'unit_price' => 'Price the customer will pay per unit',
            'size' => 'WxH (in mm)',
            'skin.quantity' => 'Skin Quantity (this will be multiplied by Product Quantity)',
            'skin.substrate' => 'Skin Substrate (exact component code)',
            'skin.printer' => 'Skin Printer (PRINT or leave empty for blank)',
            'skin.label' => 'Skin Label (Plain, Reframe, AFI Branding or Octanorm)',
            'skin.artwork' => 'Skin Artwork Quantity',
            'frame.quantity' => 'Frame Quantity (this will be multiplied by Product Quantity)',
            'frame.extrusion' => 'Frame Extrusion (exact component code)',
            'frame.rig' => 'Frame Rig (exact component code)',
            'frame.hanging_bracket' => 'Frame Hanging Bracket (exact component code)',
            'frame.powder_coat' => 'Frame Powder Coat (exact colour name)',
            'exhibition.quantity' => 'Exhibition Fix Kit Quantity (this will be multiplied by Product Quantity)',
            'wall.quantity' => 'Wall Mount Quantity (this will be multiplied by Product Quantity)',
            'bracket.quantity' => 'Bracket Set Quantity (this will be multiplied by Product Quantity)',
        ];
    }

    public static function getSample($job)
    {
        $sample = parent::getSample($job);
        $sample[0] = ArrayHelper::merge([
            'name' => 'My Reframe SS101',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1000x2000',
            'skin.quantity' => '1',
            'skin.substrate' => 'MATTEEX',
            'skin.printer' => 'PRINT',
            'skin.label' => 'Plain',
            'skin.artwork' => '1',
            'frame.quantity' => '1',
            'frame.extrusion' => 'SS101MN',
            'frame.rig' => '',
            'frame.hanging_bracket' => '',
            'frame.powder_coat' => '',
            'exhibition.quantity' => '0',
            'wall.quantity' => '0',
            'bracket.quantity' => '0',
        ], $sample[0]);
        $sample[1] = ArrayHelper::merge([
            'name' => 'Another Reframe SS101',
            'quantity' => '1',
            'unit_price' => '123.45',
            'size' => '1500x1500',
            'skin.quantity' => '1',
            'skin.substrate' => 'BLACKOUT',
            'skin.printer' => 'PRINT',
            'skin.label' => 'ReFrame',
            'skin.artwork' => '1',
            'frame.quantity' => '1',
            'frame.extrusion' => 'SS101MN',
            'frame.rig' => 'RP008',
            'frame.hanging_bracket' => 'HBGM',
            'frame.powder_coat' => 'Satin Black',
            'exhibition.quantity' => '1',
            'wall.quantity' => '0',
            'bracket.quantity' => '0',
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
            'skin.substrate' => 'ProductToOptions[new1002][valueDecoded]',
            'skin.printer' => 'ProductToOptions[new1003][valueDecoded]',
            'skin.label' => 'ProductToOptions[new1004][valueDecoded]',
            'skin.artwork' => 'ProductToOptions[new1005][quantity]',
            'frame.quantity' => 'Items[new1][quantity]',
            'frame.extrusion' => 'ProductToOptions[new1007][valueDecoded]',
            'frame.rig' => 'ProductToOptions[new1008][valueDecoded][component]',
            'frame.hanging_bracket' => 'ProductToOptions[new1009][valueDecoded][component]',
            'frame.powder_coat' => 'ProductToOptions[new1010][valueDecoded]',
            'exhibition.quantity' => 'Items[new2][quantity]',
            'wall.quantity' => 'Items[new3][quantity]',
            'bracket.quantity' => 'Items[new4][quantity]',
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
            $mapped['ProductToOptions']['new1002']['valueDecoded'] = $substrate->id;
        }
        $printer = Component::findOne(['code' => $row['skin.printer']]);
        if ($printer) {
            $mapped['ProductToOptions']['new1003']['valueDecoded'] = $printer->id;
        }
        $extrusion = Component::findOne(['code' => $row['frame.extrusion']]);
        if ($extrusion) {
            $mapped['ProductToOptions']['new1007']['valueDecoded'] = $extrusion->id;
        }
        if (!empty($row['frame.rig'])) {
            $rig = Component::findOne(['code' => $row['frame.rig']]);
            if ($rig) {
                $mapped['ProductToOptions']['new1008']['valueDecoded']['component'] = $rig->id;
            }
        }
        if (!empty($row['frame.hanging_bracket'])) {
            $hangingBracket = Component::findOne(['code' => $row['frame.hanging_bracket']]);
            if ($hangingBracket) {
                $mapped['ProductToOptions']['new1009']['valueDecoded']['component'] = $hangingBracket->id;
            }
        }
        $mapped['ProductToOptions']['new1006']['valueDecoded']['value'] = 55;
        if (empty($mapped['ProductToOptions']['new1004']['valueDecoded'])) {
            $mapped['ProductToOptions']['new1004']['valueDecoded'] = 'Reframe';
        }
        return $mapped;
    }

}