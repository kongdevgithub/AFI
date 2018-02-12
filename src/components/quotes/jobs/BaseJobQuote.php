<?php

namespace app\components\quotes\jobs;

use app\components\fields\ComponentField;
use app\components\Helper;
use app\components\quotes\products\BaseProductQuote;
use app\components\quotes\products\RateProductQuote;
use app\models\ItemType;
use app\models\Job;
use app\models\Option;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * BaseJobQuote
 */
class BaseJobQuote extends Component
{

    /**
     * @return array
     */
    public static function opts()
    {
        static $opts;
        if ($opts === null) {
            $opts = [];
            foreach (FileHelper::findFiles(__DIR__, ['recursive' => false]) as $file) {
                $file = basename($file);
                $opts[__NAMESPACE__ . '\\' . str_replace('.php', '', $file)] = str_replace('JobQuote.php', '', $file);
            }
        }
        $opts['app\components\quotes\jobs\BaseJobQuote'] = 'Job';
        asort($opts);
        return $opts;
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getQuoteCost($job)
    {
        $quote = 0;
        foreach ($job->products as $product) {
            $quote += $product->quote_total_cost;
        }
        return $quote;
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getQuotePrice($job)
    {
        $quote = 0;
        foreach ($job->products as $product) {
            $quote += $product->quote_factor_price - $product->quote_discount_price;
        }
        return $quote;
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getQuoteWeight($job)
    {
        $weight = 0;
        foreach ($job->products as $product) {
            $weight += $product->quote_weight;
        }
        return $weight;
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getQuoteFactor($job)
    {
        return 1;
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getQuoteMarkup($job)
    {
        return $job->priceStructure->markup;
    }

    /**
     * @param Job $job
     * @return string
     */
    public function getQuoteLabel($job = null)
    {
        return '<span title="' . Html::encode($this->getDescription($job)) . '" data-toggle="tooltip">' . Html::encode($this->getName($job)) . '</span>';
    }

    /**
     * @param Job $job
     * @return string
     */
    public function getName($job = null)
    {
        return BaseJobQuote::opts()[static::className()];
    }

    /**
     * @param Job $job
     * @return string
     */
    public function getDescription($job = null)
    {
        return BaseJobQuote::opts()[static::className()];
    }

    /**
     * @param Job $job
     * @param bool $verbose
     * @throws Exception
     */
    public function saveQuote($job, $verbose = false)
    {
        // wait for another process to finish
        for ($i = 0; $i < 30; $i++) {
            if ($job->quote_generated == 0 || $job->quote_generated == 1) break;
            sleep(1);
            $job->refresh();
        }
        if ($job->quote_generated != 0 && $job->quote_generated != 1) {
            $job->quote_generated = 0;
            if (!$job->save(false)) {
                throw new Exception('Cannot save job-' . $job->id . ': ' . Helper::getErrorString($job));
            }
            throw new Exception('Cannot generate quote for job-' . $job->id . ', gave up waiting for quote_generated to become 0 or 1.');
        }

        // start generating
        $job->quote_generated = 2;
        if (!$job->save(false)) {
            throw new Exception('Cannot save job-' . $job->id . ': ' . Helper::getErrorString($job));
        }

        // save quote classes
        $this->saveQuoteClasses($job);

        // save Product quotes
        foreach ($job->products as $product) {
            // auto-apply rate
            if ($product->getRate()) {
                $product->quote_class = RateProductQuote::className();
                if ($verbose) {
                    echo 'R+';
                }
            } elseif ($product->quote_class == RateProductQuote::className()) {
                // remove rate
                $product->quote_class = BaseProductQuote::className();
                if ($verbose) {
                    echo 'R-';
                }
            }
            /** @var BaseProductQuote $productQuote */
            $productQuote = new $product->quote_class;
            $productQuote->saveQuote($product, $verbose);
            // save forkQuantityProducts
            foreach ($product->forkQuantityProducts as $_product) {
                // auto-apply rate
                if ($_product->getRate()) {
                    $_product->quote_class = RateProductQuote::className();
                    if ($verbose) {
                        echo 'R+';
                    }
                } elseif ($_product->quote_class == RateProductQuote::className()) {
                    // remove rate
                    $_product->quote_class = BaseProductQuote::className();
                    if ($verbose) {
                        echo 'R-';
                    }
                }
                $productQuote = new $_product->quote_class;
                $productQuote->saveQuote($_product, $verbose);
            }
        }

        if (!$job->vid) {
            $job->vid = $job->generateVid();
        }
        $job->quote_label = $this->getQuoteLabel($job);
        $job->quote_factor = $this->getQuoteFactor($job);
        $job->quote_markup = $this->getQuoteMarkup($job);
        $job->quote_total_cost = $this->getQuoteCost($job);
        $job->quote_wholesale_price = $this->getQuotePrice($job);
        $job->quote_weight = $this->getQuoteWeight($job);

        // set prices
        $job->quote_retail_price = $job->quote_wholesale_price * $job->quote_markup;
        $job->quote_factor_price = $job->quote_wholesale_price * $job->quote_factor;
        //$job->quote_freight_price = 0;
        //$job->quote_surcharge_price = 0;
        //$job->quote_discount_price = 0;

        // max discount
        $price = $job->quote_retail_price + $job->getProductDiscount() * $job->quote_markup;
        $job->quote_maximum_discount_price = floor(($price - ($price * $job->quote_factor)) / 10) * 10;

        // set totals
        $totalEx = $job->quote_retail_price + $job->quote_freight_price + $job->quote_surcharge_price - $job->quote_discount_price;
        $job->quote_tax_price = $job->excludes_tax ? 0 : $totalEx * 0.1;
        $job->quote_total_price = $totalEx + $job->quote_tax_price;

        // if the status is still zero then mark it as done
        $jobCheck = Job::findOne($job->id);
        $job->quote_generated = $jobCheck->quote_generated == 2 ? 1 : 0;
        $job->gearman_quote = null;

        if (!$job->save(false)) {
            throw new Exception('Cannot save job-' . $job->id . ': ' . Helper::getErrorString($job));
        }
        if ($verbose) {
            echo 'J';
        }
    }

    /**
     * @param Job $job
     * @throws Exception
     */
    public function saveQuoteClasses($job)
    {
        foreach ($job->products as $product) {
            foreach ($product->items as $item) {

                // move EM Print option to correct workflow
                $optionEmPrint = $item->getProductToOption(Option::OPTION_EM_PRINT);
                if ($optionEmPrint) {
                    if ($optionEmPrint->valueDecoded) {
                        if ($item->item_type_id != ItemType::ITEM_TYPE_EM_PRINT) {
                            $itemType = ItemType::findOne(ItemType::ITEM_TYPE_EM_PRINT);
                            $workflow = 'item-' . Inflector::variablize($itemType->name);
                            if (Yii::$app->workflowSource->getWorkflow($workflow)) {
                                $item->item_type_id = $itemType->id;
                                $item->sendToStatus(null);
                                $item->enterWorkflow($workflow);
                                if (!$item->save(false)) {
                                    throw new Exception('Cannot save item-' . $item->id . ': ' . Helper::getErrorString($item));
                                }
                            }
                        }
                    } else {
                        if ($item->item_type_id != ItemType::ITEM_TYPE_PRINT) {
                            $itemType = ItemType::findOne(ItemType::ITEM_TYPE_PRINT);
                            $workflow = 'item-' . Inflector::variablize($itemType->name);
                            if (Yii::$app->workflowSource->getWorkflow($workflow)) {
                                $item->item_type_id = $itemType->id;
                                $item->sendToStatus(null);
                                $item->enterWorkflow($workflow);
                                if (!$item->save(false)) {
                                    throw new Exception('Cannot save item-' . $item->id . ': ' . Helper::getErrorString($item));
                                }
                            }
                        }
                    }
                }

                // populate productToOption.quote_class
                foreach ($item->productToOptions as $productToOption) {
                    if ($productToOption->option->field_class && !$productToOption->quote_class) {
                        /** @var ComponentField $field */
                        $field = new $productToOption->option->field_class;
                        if (!$field instanceof ComponentField) {
                            continue;
                        }
                        $productToOption->quote_class = $field->getQuoteClass($productToOption);
                        if (!$productToOption->save(false)) {
                            throw new Exception('Cannot save productToOption-' . $productToOption->id . ': ' . Helper::getErrorString($productToOption));
                        }
                    }
                }

                // populate productToComponent.quote_class
                foreach ($item->productToComponents as $productToComponent) {
                    if (!$productToComponent->quote_class) {
                        $productToComponent->quote_class = $productToComponent->getQuoteClass();
                        if (!$productToComponent->save(false)) {
                            throw new Exception('Cannot save productToComponent-' . $productToComponent->id . ': ' . Helper::getErrorString($productToComponent));
                        }
                    }
                }
            }
        }
    }
}