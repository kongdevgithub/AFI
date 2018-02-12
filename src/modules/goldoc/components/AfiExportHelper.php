<?php

namespace app\modules\goldoc\components;

use app\components\BulkQuoteHelper;
use app\components\Helper;
use app\models\form\ProductForm;
use app\models\Job;
use app\models\Link;
use app\modules\goldoc\models\Product;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;


/**
 * AfiExportHelper
 * @package app\components
 */
class AfiExportHelper
{

    public static $emPrintItems = [
        62, // FNS - Fence fabric - Faber
        52, // CBJ – CCB jacket – olive or Faber
        70, // PBA -pole brackets –  Eurotech
        76, // RRW – road race wrap – olive or Faber
    ];
    public static $emHardwareItems = [
        50, // BNP – banner pole -  Formenta – Glenn has ordered I believe. 4 for UAC from AFI direct to UAC for delivery to site am 11th January
    ];
    public static $customTypes = [
        2, // TSS - Technical support Structure
        3, // CTM - Custom
    ];
    public static $fabricationQuantities = [
        47 => 1,
        82 => 3,
    ];
    public static $fabricationSubstrates = [
        41,
    ];


    /**
     * @param Product $product
     * @return mixed
     * @throws Exception
     */
    public static function addProductToJob($product)
    {
        if ($product->supplier_id != 1) return false;
        if ($product->supplier_reference) return false;

        $name = $product->venue->label;
        if (in_array($product->item_id, ArrayHelper::merge(AfiExportHelper::$emPrintItems, AfiExportHelper::$emHardwareItems))) {
            if ($product->venue->code != 'UAC') {
                $name .= ' (EM)';
            }
        } elseif (in_array($product->type_id, AfiExportHelper::$customTypes)) {
            $name .= ' (CTM)';
        }

        $transactions = Helper::beginTransactions();
        $afiJob = static::getAfiJob($name);

        $isEmPrint = in_array($product->item_id, static::$emPrintItems);
        $isEmHardware = in_array($product->item_id, static::$emHardwareItems);
        if ($product->venue->code == 'UAC') {
            $isEmPrint = false;
            $isEmHardware = false;
        }
        $isFabrication = in_array($product->item_id, static::$fabricationQuantities) || in_array($product->substrate_id, static::$fabricationSubstrates);
        $isPrint = !in_array($product->substrate_id, static::$fabricationSubstrates) && !$isEmPrint && !$isEmHardware;

        $details = [];
        $details[] = $product->details;
        if ($product->venue) {
            $details[] = 'venue: ' . $product->venue->label;
        }
        if ($product->loc) {
            $details[] = 'loc: ' . $product->loc;
        }
        $width = $product->width ?: 0;
        $height = $product->height ?: 0;
        if ($product->venue_id != 133 && $product->item_id == 52 && $height == 950) {
            $height = 965;
        }
        $afiProductAttributes = ArrayHelper::merge(BulkQuoteHelper::getProductTemplate(142), [
            'Product' => [
                'job_id' => $afiJob->id,
                'name' => $product->getName() . ' - LOC: ' . $product->loc,
                'details' => trim(implode("\n", $details)),
                'quantity' => $product->quantity,
                'quote_retail_unit_price_import' => $product->product_price / $product->quantity,
            ],
            'Items' => [
                // print
                'new0' => [
                    'name' => 'PRINT-' . $product->getCode(),
                    'quantity' => $isPrint ? 1 : 0,
                ],
                // fabrication
                'new1' => [
                    'name' => 'EXTRUSION-' . $product->getCode(),
                    'quantity' => in_array($product->item_id, static::$fabricationQuantities) ? static::$fabricationQuantities[$product->item_id] : ($isFabrication ? 1 : 0),
                ],
                // em print
                'new2' => [
                    'name' => 'EMPRINT-' . $product->getCode(),
                    'quantity' => $isEmPrint ? 1 : 0,
                ],
                // em hardware
                'new3' => [
                    'name' => 'EMHARDWARE-' . $product->getCode(),
                    'quantity' => $isEmHardware ? 1 : 0,
                ],
            ],
            'ProductToOptions' => [
                // size
                'new0' => [
                    'valueDecoded' => [
                        'value' => 2,
                        'width' => $width,
                        'height' => $height,
                        //'depth' => $product->depth ?: 0,
                    ],
                ],
            ],
        ]);

        $afiProductForm = new ProductForm();
        $afiProductForm->product = new \app\models\Product();
        $afiProductForm->setAttributes($afiProductAttributes);
        if (!$afiProductForm->save()) {
            throw new Exception('Cannot save Product Form:' . Helper::getErrorString($afiProductForm->getAllModels()));
        }

        // artwork
        if ($product->artwork) {
            foreach ($afiProductForm->product->items as $item) {
                $product->artwork->copy(['Attachment' => [
                    'model_name' => $item->className() . '-Artwork',
                    'model_id' => $item->id,
                ]]);
            }
        }

        // link from V4 to GOLDOC
        $link = new Link();
        $link->model_name = $afiProductForm->product->className();
        $link->model_id = $afiProductForm->product->id;
        $link->title = 'GOLDOC-' . $product->id;
        $link->url = 'https://afi.ink/goldoc/product/view?id=' . $product->id;
        if (!$link->save()) {
            throw new Exception('Cannot save Link:' . Helper::getErrorString($link));
        }

        // link from GOLDOC to V4
        $product->status = 'goldoc-product/production';
        $product->supplier_reference = $afiProductForm->product->id;
        $product->save(false);

        // move job to production pending
        if ($afiJob->status != 'job/production') {
            $afiJob->status = 'job/productionPending';
            $afiJob->initStatus();
            if (!$afiJob->save()) {
                throw new Exception('Cannot save Job:' . Helper::getErrorString($afiJob));
            }
        }

        Helper::commitTransactions($transactions);

        return $afiJob;
    }

    /**
     * @param $name
     * @return Job
     * @throws Exception
     */
    private static function getAfiJob($name)
    {
        $afiJob = Job::find()
            ->notDeleted()
            ->andWhere(['not', ['status' => 'job/cancelled']])
            ->andWhere([
                'company_id' => 13227,
                'contact_id' => 17071,
                'name' => $name,
            ])
            ->one();
        if ($afiJob) {
            return $afiJob;
        }

        $afiJob = new Job;
        $afiJob->name = $name;
        $afiJob->quote_win_chance = 75;
        $afiJob->company_id = 13227;
        $afiJob->contact_id = 17071;
        $afiJob->staff_rep_id = 21;
        $afiJob->staff_csr_id = 21;
        $afiJob->account_term_id = 1;
        $afiJob->price_structure_id = 2;
        $afiJob->quote_markup = 1;
        $afiJob->due_date = '2017-02-09';
        $afiJob->loadDefaultValues();
        if (!$afiJob->save()) {
            throw new Exception('Cannot save Job:' . Helper::getErrorString($afiJob));
        }
        return $afiJob;
    }

}