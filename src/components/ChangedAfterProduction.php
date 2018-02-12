<?php

namespace app\components;


use app\models\Item;
use app\models\Job;
use app\models\Product;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use bedezign\yii2\audit\models\AuditTrail;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class ChangedAfterProduction
 * @package app\components
 */
class ChangedAfterProduction
{

    /**
     * @param ActiveRecord $model
     * @param array $fields
     * @param string $start
     * @return array
     */
    public static function checkAuditTrails($model, $fields, $start = null)
    {
        $query = AuditTrail::find()
            ->andWhere([
                'model' => $model->className(),
                'model_id' => $model->primaryKey,
                'field' => $fields,
            ]);
        if ($start) {
            $query->andWhere('created > :start', [':start' => Yii::$app->formatter->asDate($start, 'php:Y-m-d')]);
        }
        $changes = [];
        debug($query->createCommand()->getRawSql()); die;

        foreach ($query->all() as $auditTrail) {
            $changes[] = $auditTrail->attributes;
        }
        return $changes;
    }

    /**
     * @param Job $job
     * @param null $start
     * @return array
     */
    public static function checkJob($job, $start = null)
    {
        $changes = static::checkAuditTrails($job, ['name', 'due_date'], $start);
        foreach ($job->products as $product) {
            foreach (static::checkProduct($product, $start) as $change) {
                $changes[] = $change;
            }
        }
        return $changes;
    }

    /**
     * @param Product $product
     * @param null $start
     * @return array
     */
    public static function checkProduct($product, $start = null)
    {
        $changes = static::checkAuditTrails($product, ['name', 'due_date'], $start);
        foreach ($product->items as $item) {
            foreach (static::checkItem($item, $start) as $change) {
                $changes[] = $change;
            }
        }
        foreach ($product->productToOptions as $productToOption) {
            foreach (static::checkProductToOption($productToOption, $start) as $change) {
                $changes[] = $change;
            }
        }
        foreach ($product->productToComponents as $productToComponent) {
            foreach (static::checkProductToComponent($productToComponent, $start) as $change) {
                $changes[] = $change;
            }
        }
        return $changes;
    }

    /**
     * @param Item $item
     * @param null $start
     * @return array
     */
    public static function checkItem($item, $start = null)
    {
        $changes = static::checkAuditTrails($item, ['name', 'due_date'], $start);
        foreach ($item->productToOptions as $productToOption) {
            foreach (static::checkProductToOption($productToOption, $start) as $change) {
                $changes[] = $change;
            }
        }
        foreach ($item->productToComponents as $productToComponent) {
            foreach (static::checkProductToComponent($productToComponent, $start) as $change) {
                $changes[] = $change;
            }
        }
        return $changes;
    }

    /**
     * @param ProductToOption $productToOption
     * @param null $start
     * @return array
     */
    public static function checkProductToOption($productToOption, $start = null)
    {
        return static::checkAuditTrails($productToOption, ['name'], $start);
    }

    /**
     * @param ProductToComponent $productToComponent
     * @param null $start
     * @return array
     */
    public static function checkProductToComponent($productToComponent, $start = null)
    {
        return static::checkAuditTrails($productToComponent, ['name'], $start);
    }

}