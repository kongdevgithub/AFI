<?php

namespace app\components;

use app\models\Component;
use app\models\Item;
use app\models\Job;
use app\models\Product;
use yii\helpers\ArrayHelper;

/**
 * CsvManager
 */
class CsvManager extends \yii\base\Component
{

    /**
     * @param Item|Product|Job|Component|\app\modules\goldoc\models\Product $model
     * @return array
     */
    public static function csvRow($model)
    {
        if ($model->className() == Job::className()) {
            return static::csvRowJob($model);
        }
        if ($model->className() == Product::className()) {
            return static::csvRowProduct($model);
        }
        if ($model->className() == Item::className()) {
            return static::csvRowItem($model);
        }
        if ($model->className() == Component::className()) {
            return static::csvRowComponent($model);
        }

        if ($model->className() == \app\modules\goldoc\models\Product::className()) {
            return static::csvRowGoldocProduct($model);
        }
        return [];
    }

    /**
     * @param Job $job
     * @return array
     */
    protected static function csvRowJob($job)
    {
        $csv = $job->getCache('CsvManager.row', true);
        if ($csv) {
            return $csv;
        }
        return $job->setCache('CsvManager.row', [
            'job.id' => $job->id,
            'job.vid' => $job->vid,
            'job.name' => $job->name,
            'job.purchase_order' => $job->purchase_order,
            'job.quote_class' => $job->quote_class,
            'job.quote_markup' => $job->quote_markup,
            'job.quote_factor' => $job->quote_factor,
            'job.quote_factor_price' => $job->quote_factor_price,
            'job.quote_freight_price' => $job->quote_freight_price,
            'job.quote_discount_price' => $job->quote_discount_price,
            'job.quote_surcharge_price' => $job->quote_surcharge_price,
            'job.quote_tax_price' => $job->quote_tax_price,
            'job.quote_total_cost' => $job->quote_total_cost,
            'job.quote_wholesale_price' => $job->quote_wholesale_price,
            'job.quote_retail_price' => $job->quote_retail_price,
            'job.quote_total_price' => $job->quote_total_price,
            'job.quote_maximum_discount_price' => $job->quote_maximum_discount_price,
            'job.quote_win_chance' => $job->quote_win_chance,
            'job.quote_lost_reason' => $job->quote_lost_reason,
            'job.status' => $job->status,
            'job.version_job_id' => $job->fork_version_job_id,
            'job.copy_job_id' => $job->copy_job_id,
            'job.redo_job_id' => $job->redo_job_id,
            'job.redo_reason' => $job->redo_reason,
            'job.freight_method' => $job->freight_method,
            'job.freight_notes' => $job->freight_notes,
            'job.invoice_sent' => $job->invoice_sent,
            'job.invoice_paid' => $job->invoice_paid,
            'job.invoice_reference' => $job->invoice_reference,
            'job.invoice_amount' => $job->invoice_amount,
            'job.production_days' => $job->production_days,
            'job.prebuild_days' => $job->prebuild_days,
            'job.freight_days' => $job->freight_days,
            'job.production_date' => $job->production_date,
            'job.prebuild_date' => $job->prebuild_date,
            'job.despatch_date' => $job->despatch_date,
            'job.due_date' => $job->due_date,
            'job.installation_date' => $job->installation_date,
            'job.created_at' => $job->created_at,
            'job.quote_at' => $job->quote_at,
            'job.production_at' => $job->production_at,
            'job.despatch_at' => $job->despatch_at,
            'job.packed_at' => $job->packed_at,
            'job.complete_at' => $job->complete_at,
            'job.feedback_at' => $job->feedback_at,
            'job.deleted_at' => $job->deleted_at,
            'staffLead.id' => $job->staffLead ? $job->staffLead->id : '',
            'staffLead.name' => $job->staffLead ? $job->staffLead->label : '',
            'staffRep.id' => $job->staffRep ? $job->staffRep->id : '',
            'staffRep.name' => $job->staffRep ? $job->staffRep->label : '',
            'staffCsr.id' => $job->staffCsr ? $job->staffCsr->id : '',
            'staffCsr.name' => $job->staffCsr ? $job->staffCsr->label : '',
            'staffDesigner.id' => $job->staffDesigner ? $job->staffDesigner->id : '',
            'staffDesigner.name' => $job->staffDesigner ? $job->staffDesigner->label : '',
            'company.id' => $job->company ? $job->company->id : '',
            'company.name' => $job->company ? $job->company->name : '',
            'contact.id' => $job->contact ? $job->contact->id : '',
            'contact.name' => $job->contact ? $job->contact->label : '',
            'accountTerm.id' => $job->accountTerm ? $job->accountTerm->id : '',
            'accountTerm.name' => $job->accountTerm ? $job->accountTerm->name : '',
            'priceStructure.id' => $job->priceStructure ? $job->priceStructure->id : '',
            'priceStructure.name' => $job->priceStructure ? $job->priceStructure->name : '',
            'jobType.id' => $job->jobType ? $job->jobType->id : '',
            'jobType.name' => $job->jobType ? $job->jobType->name : '',
            'shippingAddresses.state' => implode(',', ArrayHelper::map($job->shippingAddresses, 'state', 'state')),
        ], true);
    }

    /**
     * @param Product $product
     * @return array
     */
    protected static function csvRowProduct($product)
    {
        $csv = $product->getCache('CsvManager.row', true);
        if ($csv) {
            return $csv;
        }
        $size = $product->getSize();
        return $product->setCache('CsvManager.row', ArrayHelper::merge(static::csvRowJob($product->job), [
            'product.id' => $product->id,
            'product.name' => $product->name,
            'product.details' => $product->details,
            'product.quote_class' => $product->quote_class,
            'product.quantity' => $product->quantity,
            'product.quote_unit_cost' => $product->quote_unit_cost,
            'product.quote_unit_price' => $product->quote_unit_price,
            'product.quote_factor' => $product->quote_factor,
            'product.quote_factor_price' => $product->quote_factor_price,
            'product.quote_discount_price' => $product->quote_discount_price,
            'product.quote_total_cost' => $product->quote_total_cost,
            'product.quote_total_price' => $product->quote_total_price,
            'product.quote_total_price_unlocked' => $product->quote_total_price_unlocked,
            'product.status' => $product->status,
            'product.due_date' => $product->due_date,
            'product.created_at' => $product->created_at,
            'product.production_at' => $product->production_at,
            'product.despatch_at' => $product->despatch_at,
            'product.packed_at' => $product->packed_at,
            'product.complete_at' => $product->complete_at,
            'product.deleted_at' => $product->deleted_at,
            'product.prebuild_required' => $product->prebuild_required,
            'product.preserve_unit_prices' => $product->preserve_unit_prices,
            'product.area' => $product->getArea(),
            'product.perimeter' => $product->getPerimeter(),
            'product.width' => isset($size['width']) ? $size['width'] : '',
            'product.height' => isset($size['height']) ? $size['height'] : '',
            'product.depth' => isset($size['depth']) ? $size['depth'] : '',
            'productType.id' => $product->productType ? $product->productType->id : '',
            'productType.name' => $product->productType ? $product->productType->getBreadcrumbString(' > ') : '',
            'product.correction.reasons' => $product->getCorrectionReasons(),
            'product.correction.reason.internal' => $product->getCorrectionCount('internal'),
            'product.correction.reason.external' => $product->getCorrectionCount('external'),
            'product.correction.reason.none' => $product->getCorrectionCount('none'),
        ]), true);
    }

    /**
     * @param Item $item
     * @return array
     */
    protected static function csvRowItem($item)
    {
        $csv = $item->getCache('CsvManager.row', true);
        if ($csv) {
            return $csv;
        }
        $size = $item->getSize();
        return $item->setCache('CsvManager.row', ArrayHelper::merge(static::csvRowProduct($item->product), [
            'item.id' => $item->id,
            'item.name' => $item->name,
            'item.status' => $item->status,
            'item.area' => $item->getArea(),
            'item.perimeter' => $item->getPerimeter(),
            'item.width' => isset($size['width']) ? $size['width'] : '',
            'item.height' => isset($size['height']) ? $size['height'] : '',
            'item.depth' => isset($size['depth']) ? $size['depth'] : '',
            'item.quote_class' => $item->quote_class,
            'item.quote_unit_cost' => $item->quote_unit_cost,
            'item.quote_unit_price' => $item->quote_unit_price,
            'item.quote_factor' => $item->quote_factor,
            'item.quote_factor_price' => $item->quote_factor_price,
            'item.quote_total_cost' => $item->quote_total_cost,
            'item.quote_total_price' => $item->quote_total_price,
            'item.quote_total_price_unlocked' => $item->quote_total_price_unlocked,
            'item.quantity' => $item->quantity,
            'item.purchase_order' => $item->purchase_order,
            'item.created_at' => $item->created_at,
            'item.order_at' => $item->order_at,
            'item.production_at' => $item->production_at,
            'item.despatch_at' => $item->despatch_at,
            'item.packed_at' => $item->packed_at,
            'item.complete_at' => $item->complete_at,
            'item.deleted_at' => $item->deleted_at,
            'item.supply_date' => $item->supply_date,
            'item.due_date' => $item->due_date,
            'supplier.id' => $item->supplier ? $item->supplier->id : '',
            'supplier.name' => $item->supplier ? $item->supplier->name : '',
            'itemType.id' => $item->itemType->id,
            'itemType.name' => $item->itemType->name,
        ]), true);
    }


    /**
     * @param Component $component
     * @return array
     */
    protected static function csvRowComponent($component)
    {
        $csv = $component->getCache('CsvManager.row', true);
        if ($csv) {
            return $csv;
        }
        return $component->setCache('CsvManager.row', [
            'component.id' => $component->id,
            'component.code' => $component->code,
            'component.name' => $component->name,
            'component.brand' => $component->brand,
            'component.status' => $component->status,
            'component.unit_cost' => $component->unit_cost,
            'component.quantity_factor' => $component->quantity_factor,
            'component.component_config' => $component->component_config,
            'component.quote_class' => $component->quote_class,
            'component.make_ready_cost' => $component->make_ready_cost,
            'component.minimum_cost' => $component->minimum_cost,
            'component.unit_weight' => $component->unit_weight,
            'component.unit_dead_weight' => $component->unit_dead_weight,
            'component.unit_cubic_weight' => $component->unit_cubic_weight,
            'component.unit_of_measure' => $component->unit_of_measure,
            'component.track_stock' => $component->track_stock,
            'component.notes' => $component->notes,
            'component.created_at' => $component->created_at,
            'component.updated_at' => $component->updated_at,
            'componentType.id' => $component->componentType ? $component->componentType->id : '',
            'componentType.name' => $component->componentType ? $component->componentType->name : '',
        ], true);
    }

    /**
     * @param \app\modules\goldoc\models\Product $product
     * @return array
     */
    protected static function csvRowGoldocProduct($product)
    {
        $csv = $product->getCache('CsvManager.row', true);
        if ($csv) {
            return $csv;
        }
        $attributes = [
            'product.id' => $product->id,
            'product.name' => $product->name,
            'product.quantity' => $product->quantity,
            'product.details' => $product->details,
            'product.loc' => $product->loc,
            'product.width' => $product->width,
            'product.height' => $product->height,
            'product.depth' => $product->depth,
            'product.comments' => $product->comments,
            'product.status' => $product->status,
            'product.product_unit_price' => $product->product_unit_price,
            'product.product_price' => $product->product_price,
            'product.labour_price' => $product->labour_price,
            'product.machine_price' => $product->machine_price,
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
            'product.installer.id' => $product->installer_id,
            'product.installer.code' => $product->installer ? $product->installer->code : '',
            'product.installer.name' => $product->installer ? $product->installer->name : '',
        ];
        $additionalAttributes = [
            'installer_standard_hours',
            'installer_specialist_hours',
            'bump_out_hours',
            'scissor_lift_hours',
            'rt_scissor_lift_hours',
            'small_boom_hours',
            'large_boom_hours',
            'flt_hours',
            'supplier_priced',
        ];
        foreach ($additionalAttributes as $attribute) {
            $attributes['product.' . $attribute] = $product->$attribute;
        }
        return $product->setCache('CsvManager.row', $attributes, true);
    }

}