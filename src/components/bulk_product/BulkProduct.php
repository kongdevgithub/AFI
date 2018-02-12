<?php

namespace app\components\bulk_product;


use app\components\quotes\jobs\BaseJobQuote;
use app\models\Component;
use app\models\form\JobForm;
use app\models\form\ProductForm;
use app\models\Job;
use app\models\Option;
use app\models\Product;
use app\models\ProductType;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class BulkQuoteHelper
 * @package app\components
 */
abstract class BulkProduct extends Object
{

    const PRODUCT_TYPE_ID = 0;

    public static function getDoc()
    {
        return [];
    }

    /**
     * @param Job $job
     * @return array
     */
    public static function getSample($job)
    {
        $sample = [
            [],
            [],
        ];
        if ($job) {
            foreach ($job->shippingAddresses as $shippingAddress) {
                $sample[0]['D: ' . $shippingAddress->name] = 0;
                $sample[1]['D: ' . $shippingAddress->name] = 0;
            }
        }
        return $sample;
    }

    public static function getMap()
    {
        return [
            'name' => 'Product[name]',
            'quantity' => 'Product[quantity]',
            'unit_price' => 'Product[quote_retail_unit_price_import]',
        ];
    }

    /**
     * @param Job $job
     * @param $row
     * @return ProductForm
     */
    public static function getProductForm($job, $row)
    {
        $productForm = new ProductForm();
        $productForm->product = new Product;
        $productForm->product->loadDefaultValues();
        $attributes = ArrayHelper::merge([
            'Product' => ['job_id' => $job->id],
        ], static::getProductFormAttributes($row));
        $productForm->setAttributes($attributes);
        return $productForm;
    }

    /**
     * @param array $row
     * @return array
     */
    public static function getProductFormAttributes($row)
    {
        $mapped = static::getProductFormTemplate();
        foreach (static::getMap() as $k => $v) {
            if (!isset($row[$k])) continue;
            parse_str(static::getMap()[$k] . '=' . urlencode($row[$k]), $_mapped);
            $mapped = ArrayHelper::merge($mapped, $_mapped);
        }
        return $mapped;
    }

    /**
     * @return array
     */
    public static function getProductFormTemplate()
    {
        $productType = ProductType::findOne(static::PRODUCT_TYPE_ID);

        $template = [
            'Product' => [
                'product_type_id' => $productType->id,
                'quote_class' => $productType->quote_class,
                'complexity' => '0',
            ],
            'Items' => [],
            'ProductToOptions' => [],
            'ProductToComponents' => [],
        ];

        $itemMap = [];
        foreach ($productType->productTypeToItemTypes as $k => $productTypeToItemType) {
            $itemMap[$productTypeToItemType->id] = 'new' . $k;
            $template['Items']['new' . $k] = [
                'name' => $productTypeToItemType->name,
                'product_type_to_item_type_id' => $productTypeToItemType->id,
                'item_type_id' => $productTypeToItemType->item_type_id,
                'quote_class' => $productTypeToItemType->quote_class,
                'quantity' => $productTypeToItemType->quantity,
            ];
        }
        foreach ($productType->productTypeToOptions as $k => $productTypeToOption) {
            if ($productTypeToOption->product_type_to_item_type_id) {
                $k += 1000;
            }
            $template['ProductToOptions']['new' . $k] = [
                'option_name' => $productTypeToOption->option->name,
                'option_id' => $productTypeToOption->option_id,
                'product_type_to_option_id' => $productTypeToOption->id,
                'item_id' => $productTypeToOption->product_type_to_item_type_id ? $itemMap[$productTypeToOption->product_type_to_item_type_id] : '',
                'quote_class' => $productTypeToOption->quote_class,
                'quote_quantity_factor' => $productTypeToOption->quantity_factor,
                'valueDecoded' => '',
            ];
        }
        foreach ($productType->productTypeToComponents as $k => $productTypeToComponent) {
            if ($productTypeToComponent->product_type_to_item_type_id) {
                $k += 1000;
            }
            $template['ProductToComponents']['new' . $k] = [
                'component_name' => $productTypeToComponent->component->name,
                'component_id' => $productTypeToComponent->component_id,
                'product_type_to_component_id' => $productTypeToComponent->id,
                'item_id' => $productTypeToComponent->product_type_to_item_type_id ? $itemMap[$productTypeToComponent->product_type_to_item_type_id] : '',
                'quote_class' => $productTypeToComponent->quote_class,
                'quantity' => $productTypeToComponent->quantity,
                'quote_quantity_factor' => $productTypeToComponent->quantity_factor,
            ];
        }

        return $template;
    }


}