<?php

namespace app\components;


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
use yii\helpers\ArrayHelper;

/**
 * Class BulkQuoteHelper
 * @package app\components
 */
class BulkQuoteHelper
{

    /**
     * @param $name
     * @param null $template
     * @return Job|null
     */
    public static function getJob($name, $template = null)
    {
        $job = Job::findOne(['name' => $name, 'deleted_at' => null]);
        if (!$job && $template) {
            $job = static::createJob($name, $template);
            $job->isNewRecord = true;
        }
        return $job;
    }

    /**
     * @param $filter
     * @return array
     */
    public static function getJobs($filter = false)
    {
        $products = [];
        foreach (static::getProducts() as $product_key => $product_data) {
            $product_template = static::getProductTemplate($product_data['Product']['product_type_id']);

            //$product_template = ArrayHelper::merge($product_template, $product_data);
            $product_template['Product'] = ArrayHelper::merge($product_template['Product'], $product_data['Product']);
            if (!empty($product_data['Items'])) {
                $product_template['Items'] = ArrayHelper::merge($product_template['Items'], $product_data['Items']);
            }
            foreach ($product_data['ProductToOptions'] as $option_id => $mergeProductToOption) {
                foreach ($product_template['ProductToOptions'] as $k => $productToOption) {
                    if ($productToOption['option_id'] == $option_id) {
                        $product_template['ProductToOptions'][$k] = ArrayHelper::merge($productToOption, $mergeProductToOption);
                    }
                }
            }

            foreach (static::getSubstrates($product_key) as $substrate_key => $substrate_data) {

                //$product_template = ArrayHelper::merge($product_template, $substrate_data);
                foreach ($substrate_data['ProductToOptions'] as $option_id => $mergeProductToOption) {
                    foreach ($product_template['ProductToOptions'] as $k => $productToOption) {
                        if ($productToOption['option_id'] == $option_id) {
                            $product_template['ProductToOptions'][$k] = ArrayHelper::merge($productToOption, $mergeProductToOption);
                        }
                    }
                }

                foreach (static::getSizes($product_key) as $size_key => $size_data) {
                    $product_template = ArrayHelper::merge($product_template, $size_data);
                    foreach (static::getQuantities($product_key) as $quantity_key => $quantity_data) {
                        $product_template = ArrayHelper::merge($product_template, $quantity_data);
                        $products[$product_key . ' ' . $substrate_key . ' ' . $size_key . ' x' . $quantity_key] = $product_template;
                    }
                }
            }
        }
        if ($filter) {
            foreach ($products as $k => $v) {
                if (strpos($k, $filter) === false) {
                    unset($products[$k]);
                }
            }
        }
        return $products;
    }

    /**
     * @param $productKey
     * @return array
     */
    public static function getSubstrates($productKey)
    {
        if (in_array($productKey, ['ILS50', 'ILS80', 'ILS120', 'ILS140'])) {
            return [
                'backlitpoly' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11841],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['POPUPAFRAME'])) {
            return [
                'btex' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11840],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FC'])) {
            return [
                'btex' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11840],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['SAVINYL'])) {
            return [
                'fabsav' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11946],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['SCFRAME'])) {
            return [
                'cottoncanvas' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11957],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['WALLPAPER'])) {
            return [
                'wallpaper' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11958],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['3X3MARQUEEROOF', '3X3MARQUEEWALL', '3X45MARQUEEROOF', '3X45MARQUEEWALL', '3X6MARQUEEROOF', '3X6MARQUEEWALL'])) {
            return [
                'marquee' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11961],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['B3BOW', 'B3SAIL', 'B3TEARDROP', 'BOWHEADSS', 'BOWHEADDS', 'EYECATCHER'])) {
            return [
                'polymesh' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11972],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['AFRAME'])) {
            return [
                'savpromo' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11951],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['CAFEBARRIER'])) {
            return [
                'pvc' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11956],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FENCEMESH'])) {
            return [
                'polymesh' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11972],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                        Option::OPTION_FENCE_EYELET => ['valueDecoded' => 500],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FLAG'])) {
            return [
                'polymesh' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11972],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FLAGOPTIONS'])) {
            return [
                'polymesh' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11972],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                        5659 => ['valueDecoded' => 11838], // flag hemmed
                        5660 => ['valueDecoded' => 11838], // flag header
                        5661 => ['valueDecoded' => 11838], // flag self pocket
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FLAGDSOPTIONS'])) {
            return [
                'polymesh' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11972],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                        5659 => ['valueDecoded' => 11838], // flag hemmed
                        5660 => ['valueDecoded' => 11838], // flag header
                        5661 => ['valueDecoded' => 11838], // flag self pocket
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FLAGTRAX'])) {
            return [
                'trilobal' => [
                    'ProductToOptions' => [
                        Option::OPTION_SUBSTRATE => ['valueDecoded' => 11850],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ],
            ];
        }
        if (in_array($productKey, ['FLATBED', 'FLATBEDCUSTOM'])) {
            $substrates = [];
            $components = Component::find()->notDeleted()->andWhere(['component_type_id' => 30])->all();
            foreach ($components as $component) {
                $substrates[$component->code] = [
                    'ProductToOptions' => [
                        Option::OPTION_FLATBED_SUBSTRATE => ['valueDecoded' => $component->id],
                        Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                        Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                    ],
                ];
            }
            return $substrates;
        }
        return [
            'mattex' => [
                'ProductToOptions' => [
                    Option::OPTION_SUBSTRATE => ['valueDecoded' => 300],
                    //Option::OPTION_PRINTER => ['valueDecoded' => 11858],
                    Option::OPTION_PRINTER => ['valueDecoded' => 11816],
                    Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
                ]
            ],
//            'illumin8' => [
//                'ProductToOptions' => [
//                    Option::OPTION_SUBSTRATE => ['valueDecoded' => 11846],
//                    Option::OPTION_PRINTER => ['valueDecoded' => 11816],
//                    Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
//                ]
//            ],
//            'blackout' => [
//                'ProductToOptions' => [
//                    Option::OPTION_SUBSTRATE => ['valueDecoded' => 200],
//                    Option::OPTION_PRINTER => ['valueDecoded' => 11816],
//                    Option::OPTION_ARTWORK => ['valueDecoded' => ['component' => '11831', 'quantity' => '1']],
//                ]
//            ],
        ];
    }

    /**
     * @param $productKey
     * @return array
     */
    public static function getSizes($productKey)
    {
        if ($productKey == 'WALL') {
            return [
                '2240x2240' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '4']]]],
                '2890x2240' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '5']]]],
            ];
        }
        if ($productKey == 'POPUPAFRAME') {
            return [
                '1300x700' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '6']]]],
                '2000x900' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '7']]]],
            ];
        }
        if ($productKey == 'FC') {
            return [
                '1000x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '8']]]],
            ];
        }
        if ($productKey == 'MAGNIPRINT') {
            return [
                '46x911' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '9']]]],
                '915x110' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '10']]]],
            ];
        }
        if ($productKey == 'ROLLUP') {
            return [
                '850x2000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '11']]]],
                '1000x2200' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '12']]]],
            ];
        }
        if (in_array($productKey, ['3X3MARQUEEROOF'])) {
            return [
                '3000x3000' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '15']],
                        'new1' => ['valueDecoded' => ['value' => '13']],
                        'new6' => ['valueDecoded' => ['value' => '14']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 1],
                        'new1' => ['quantity' => 0],
                        'new2' => ['quantity' => 1],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['3X3MARQUEEWALL'])) {
            return [
                '3000x3000' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '15']],
                        'new1' => ['valueDecoded' => ['value' => '13']],
                        'new6' => ['valueDecoded' => ['value' => '14']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 0],
                        'new1' => ['quantity' => 1],
                        'new2' => ['quantity' => 0],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['3X45MARQUEEROOF'])) {
            return [
                '3000x4500' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '16']],
                        'new1' => ['valueDecoded' => ['value' => '17']],
                        'new6' => ['valueDecoded' => ['value' => '14']],
                        'new11' => ['valueDecoded' => ['value' => '18']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 1],
                        'new1' => ['quantity' => 0],
                        'new2' => ['quantity' => 0],
                        'new3' => ['quantity' => 1],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['3X45MARQUEEWALL'])) {
            return [
                '3000x4500' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '16']],
                        'new1' => ['valueDecoded' => ['value' => '17']],
                        'new6' => ['valueDecoded' => ['value' => '14']],
                        'new11' => ['valueDecoded' => ['value' => '18']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 0],
                        'new1' => ['quantity' => 1],
                        'new2' => ['quantity' => 1],
                        'new3' => ['quantity' => 0],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['3X6MARQUEEROOF'])) {
            return [
                '3000x6000' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '19']],
                        'new1' => ['valueDecoded' => ['value' => '20']],
                        'new6' => ['valueDecoded' => ['value' => '21']],
                        'new11' => ['valueDecoded' => ['value' => '14']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 1],
                        'new1' => ['quantity' => 0],
                        'new2' => ['quantity' => 0],
                        'new3' => ['quantity' => 1],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['3X6MARQUEEWALL'])) {
            return [
                '3000x6000' => [
                    'ProductToOptions' => [
                        'new0' => ['valueDecoded' => ['value' => '19']],
                        'new1' => ['valueDecoded' => ['value' => '20']],
                        'new6' => ['valueDecoded' => ['value' => '21']],
                        'new11' => ['valueDecoded' => ['value' => '14']],
                    ],
                    'Items' => [
                        'new0' => ['quantity' => 0],
                        'new1' => ['quantity' => 1],
                        'new2' => ['quantity' => 1],
                        'new3' => ['quantity' => 0],
                    ]
                ],
            ];
        }
        if (in_array($productKey, ['B3BOW'])) {
            return [
                '760x2250' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '23']]]],
                '760x3260' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '24']]]],
                '760x4840' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '22']]]],
            ];
        }
        if (in_array($productKey, ['B3SAIL'])) {
            return [
                '740x1580' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '25']]]],
                '790x2540' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '26']]]],
                '840x3920' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '27']]]],
            ];
        }
        if (in_array($productKey, ['B3TEARDROP'])) {
            return [
                '640x2020' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '28']]]],
                '1000x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '29']]]],
                '1300x4200' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '30']]]],
            ];
        }
        if (in_array($productKey, ['BOWHEADSS', 'BOWHEADDS'])) {
            return [
                '850x2400' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '31']]]],
                '850x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '32']]]],
                '850x4200' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '33']]]],
            ];
        }
        if (in_array($productKey, ['AFRAME'])) {
            return [
                '600x900' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '34']]]],
            ];
        }
        if (in_array($productKey, ['EYECATCHER'])) {
            return [
                '850x2000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '35']]]],
                '850x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '36']]]],
                '850x4000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '37']]]],
            ];
        }
        if (in_array($productKey, ['CAFEBARRIER'])) {
            return [
                '1000x940' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '38']]]],
                '2000x940' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '39']]]],
            ];
        }
        if (in_array($productKey, ['FLAGTRAX'])) {
            return [
                '1000x2000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '40']]]],
                '1000x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '41']]]],
                '1110x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '42']]]],
                '1110x3800' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '43']]]],
                '1050x4790' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '44']]]],
                '1200x4790' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '45']]]],
            ];
        }
        if (in_array($productKey, ['FLATBED', 'FLATBEDCUSTOM'])) {
            return [
                '600x900 - common market' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '600', 'height' => '900']]]],
                '841x1189 - A0' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '841', 'height' => '1189']]]],
                '594x841 - A1' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '594', 'height' => '841']]]],
                '420x594 - A2' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '420', 'height' => '594']]]],
                '297x420 - A3' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '297', 'height' => '420']]]],
                '1500x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '1500', 'height' => '1000']]]],
                '2400x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '2400', 'height' => '1000']]]],
                '960x2460 - octawall' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '960', 'height' => '2460']]]],
            ];
        }
        return [
            '1200x500' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '1200', 'height' => '500']]]],
            '1000x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '1000', 'height' => '1000']]]],
            '2000x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '2000', 'height' => '1000']]]],
            '2400x1200' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '2400', 'height' => '1200']]]],
            '3000x1500' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '3000', 'height' => '1500']]]],
            '3000x2400' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '3000', 'height' => '2400']]]],
            '4000x1000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '4000', 'height' => '1000']]]],
            '6000x2400' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '6000', 'height' => '2400']]]],
            '8000x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '8000', 'height' => '3000']]]],
            '10000x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '10000', 'height' => '3000']]]],
            '15000x3000' => ['ProductToOptions' => ['new0' => ['valueDecoded' => ['value' => '2', 'width' => '15000', 'height' => '3000']]]],
        ];
    }

    /**
     * @param $productKey
     * @return array
     */
    public static function getQuantities($productKey)
    {
        return [
            1 => ['Product' => ['quantity' => 1]],
            3 => ['Product' => ['quantity' => 3]],
            5 => ['Product' => ['quantity' => 5]],
            10 => ['Product' => ['quantity' => 10]],
            25 => ['Product' => ['quantity' => 25]],
            50 => ['Product' => ['quantity' => 50]],
            75 => ['Product' => ['quantity' => 75]],
            100 => ['Product' => ['quantity' => 100]],
        ];
    }

    /**
     * @return array
     */
    public static function getProducts()
    {
        return [
            //'SS101' => ['Product' => ['product_type_id' => 18], 'ProductToOptions' => ['3' => ['valueDecoded' => '11851']]],
            //'SS103' => ['Product' => ['product_type_id' => 19], 'ProductToOptions' => ['3' => ['valueDecoded' => '11867']]],
            //'SS105' => ['Product' => ['product_type_id' => 20], 'ProductToOptions' => ['3' => ['valueDecoded' => '11869']]],
            //'SS106' => ['Product' => ['product_type_id' => 21], 'ProductToOptions' => ['3' => ['valueDecoded' => '11870']]],
            //'SS108' => ['Product' => ['product_type_id' => 22], 'ProductToOptions' => ['3' => ['valueDecoded' => '11871']]],
            //'SS201' => ['Product' => ['product_type_id' => 23], 'ProductToOptions' => ['3' => ['valueDecoded' => '11876']]],
            //'DS101' => ['Product' => ['product_type_id' => 14], 'ProductToOptions' => ['3' => ['valueDecoded' => '11885'], '33' => ['valueDecoded' => '51']]],
            //'DS102' => ['Product' => ['product_type_id' => 37], 'ProductToOptions' => ['3' => ['valueDecoded' => '11888'], '33' => ['valueDecoded' => '51']]],
            //'DS103' => ['Product' => ['product_type_id' => 16], 'ProductToOptions' => ['3' => ['valueDecoded' => '11889']]],
            //'DS110' => ['Product' => ['product_type_id' => 17], 'ProductToOptions' => ['3' => ['valueDecoded' => '11890']]],
            //'ILS50' => ['Product' => ['product_type_id' => 24], 'ProductToOptions' => ['3' => ['valueDecoded' => '11907'], '23' => ['valueDecoded' => 'auto']]],
            //'ILS80' => ['Product' => ['product_type_id' => 30], 'ProductToOptions' => ['3' => ['valueDecoded' => '11908'], '24' => ['valueDecoded' => 'auto']]],
            //'ILS120' => ['Product' => ['product_type_id' => 44], 'ProductToOptions' => ['3' => ['valueDecoded' => '11909'], '24' => ['valueDecoded' => 'auto']]],
            //'ILS140' => ['Product' => ['product_type_id' => 43], 'ProductToOptions' => ['3' => ['valueDecoded' => '11910'], '24' => ['valueDecoded' => 'auto']]],
            //'KT001' => ['Product' => ['product_type_id' => 33], 'ProductToOptions' => ['3' => ['valueDecoded' => '11928']]],
            //'KT002' => ['Product' => ['product_type_id' => 34], 'ProductToOptions' => ['3' => ['valueDecoded' => '11933']]],
            //'WALL' => ['Product' => ['product_type_id' => 45], 'ProductToOptions' => []],
            //'POPUPAFRAME' => ['Product' => ['product_type_id' => 59], 'ProductToOptions' => []],
            //'FC' => ['Product' => ['product_type_id' => 60], 'ProductToOptions' => []],
            //'MAGNIPRINT' => ['Product' => ['product_type_id' => 61], 'ProductToOptions' => []],
            //'ROLLUP' => ['Product' => ['product_type_id' => 62], 'ProductToOptions' => []],
            //'SAVINYL' => ['Product' => ['product_type_id' => 63], 'ProductToOptions' => []],
            //'SCFRAME' => ['Product' => ['product_type_id' => 64], 'ProductToOptions' => []],
            //'WALLPAPER' => ['Product' => ['product_type_id' => 65], 'ProductToOptions' => []],
            //'3X3MARQUEEROOF' => ['Product' => ['product_type_id' => 84], 'ProductToOptions' => []],
            //'3X3MARQUEEWALL' => ['Product' => ['product_type_id' => 84], 'ProductToOptions' => []],
            //'3X45MARQUEEROOF' => ['Product' => ['product_type_id' => 86], 'ProductToOptions' => []],
            //'3X45MARQUEEWALL' => ['Product' => ['product_type_id' => 86], 'ProductToOptions' => []],
            //'3X6MARQUEEROOF' => ['Product' => ['product_type_id' => 87], 'ProductToOptions' => []],
            //'3X6MARQUEEWALL' => ['Product' => ['product_type_id' => 87], 'ProductToOptions' => []],
            //'B3BOW' => ['Product' => ['product_type_id' => 66], 'ProductToOptions' => []],
            //'B3SAIL' => ['Product' => ['product_type_id' => 88], 'ProductToOptions' => []],
            //'B3TEARDROP' => ['Product' => ['product_type_id' => 89], 'ProductToOptions' => []],
            //'BOWHEADSS' => ['Product' => ['product_type_id' => 90], 'ProductToOptions' => []],
            //'BOWHEADDS' => ['Product' => ['product_type_id' => 92], 'ProductToOptions' => []],
            //'AFRAME' => ['Product' => ['product_type_id' => 71], 'ProductToOptions' => []],
            //'EYECATCHER' => ['Product' => ['product_type_id' => 93], 'ProductToOptions' => []],
            //'CAFEBARRIER' => ['Product' => ['product_type_id' => 72], 'ProductToOptions' => []],
            //'FENCEMESH' => ['Product' => ['product_type_id' => 74], 'ProductToOptions' => []],
            //'FLAG' => ['Product' => ['product_type_id' => 76], 'ProductToOptions' => []],
            //'FLAGOPTIONS' => [
            //    'Product' => ['product_type_id' => 76],
            //    'ProductToOptions' => [],
            //    'Items' => [
            //        'new0' => ['quantity' => 1],
            //        'new1' => ['quantity' => 1],
            //        'new2' => ['quantity' => 1],
            //    ],
            //],
            //'FLAGDSOPTIONS' => ['Product' => ['product_type_id' => 94], 'ProductToOptions' => []],
            //'FLAGTRAX' => [
            //    'Product' => ['product_type_id' => 75],
            //    'ProductToOptions' => ['15' => ['valueDecoded' => '11994']],
            //    'Items' => [
            //        'new1' => ['quantity' => 0],
            //    ],
            //],
            'FLATBED' => ['Product' => ['product_type_id' => 105], 'ProductToOptions' => []],
            'FLATBEDCUSTOM' => ['Product' => ['product_type_id' => 125], 'ProductToOptions' => []],
        ];
    }

    /**
     * @return array
     */
    public static function getJobTemplate()
    {
        return [
            'Job' => [
                'name' => 'job name',
                'company_id' => '1',
                'contact_id' => '1',
                'account_term_id' => '1',
                'price_structure_id' => '1',
                'quote_class' => 'app\components\quotes\jobs\TieredJobQuote',
                //'quote_class' => 'app\components\quotes\jobs\BaseJobQuote',
                'purchase_order' => '',
                'staff_csr_id' => '1',
                'staff_rep_id' => '1',
                'staff_lead_id' => '1',
                'quote_win_chance' => '50',
                'job_type_id' => '4',
                'rollout_id' => '',
                //'due_date' => date('Y-m-d', strtotime('+2 weeks')),
            ],
            //'Addresses' => [
            //    'new1' => [
            //        'type' => 'billing',
            //        'name' => 'test',
            //        'street' => 'test',
            //        'postcode' => '1234',
            //        'city' => 'Sydney South',
            //        'state' => 'NSW',
            //        'country' => 'Australia',
            //    ],
            //    'new2' => [
            //        'type' => 'shipping',
            //        'name' => 'test',
            //        'street' => 'test',
            //        'postcode' => '1234',
            //        'city' => 'Sydney South',
            //        'state' => 'NSW',
            //        'country' => 'Australia',
            //    ],
            //],
        ];
    }

    /**
     * @param int $product_type_id
     * @return array
     */
    public static function getProductTemplate($product_type_id)
    {
        $template = [
            'Product' => [
                'quantity' => '1',
                'complexity' => '0',
                'product_type_id' => $product_type_id,
            ],
            'Items' => [],
            'ProductToOptions' => [],
            'ProductToComponents' => [],
        ];

        $productType = ProductType::findOne($product_type_id);

        $template['Product']['quote_class'] = $productType->quote_class;

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
            $template['ProductToOptions']['new' . $k] = [
                'option_id' => $productTypeToOption->option_id,
                'product_type_to_option_id' => $productTypeToOption->id,
                'item_id' => $productTypeToOption->product_type_to_item_type_id ? $itemMap[$productTypeToOption->product_type_to_item_type_id] : '',
                'quote_class' => $productTypeToOption->quote_class,
                'quote_quantity_factor' => $productTypeToOption->quantity_factor,
                'valueDecoded' => '',
            ];
        }
        foreach ($productType->productTypeToComponents as $k => $productTypeToComponent) {
            $template['ProductToComponents']['new' . $k] = [
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

    /**
     * @param $name
     * @param $template
     * @return Job
     */
    private static function createJob($name, $template)
    {
        $transaction = Yii::$app->dbData->beginTransaction();
        $jobForm = new JobForm();
        $jobForm->job = new Job;
        $jobForm->job->loadDefaultValues();
        $jobForm->setAttributes(ArrayHelper::merge(static::getJobTemplate(), ['Job' => ['name' => $name]]));
        if (!$jobForm->save()) {
            echo $jobForm->errorSummary(new ActiveForm());
            $transaction->rollBack();
            die;
        }
        static::createProduct($jobForm->job, $name, $template);
        $transaction->commit();
        //static::loadQuote($jobForm->job);
        return $jobForm->job;
    }

    /**
     * @param Job $job
     * @param $name
     * @param $template
     * @return Product
     */
    private static function createProduct($job, $name, $template)
    {
        $productForm = new ProductForm();
        $productForm->product = new Product;
        $productForm->product->loadDefaultValues();
        $productForm->setAttributes(ArrayHelper::merge($template, ['Product' => ['job_id' => $job->id, 'name' => $name]]));
        if (!$productForm->save()) {
            echo $productForm->errorSummary(new ActiveForm());
            die;
        }
        return $productForm->product;
    }

    /**
     * @param Job $job
     */
    public static function loadQuote($job)
    {
        /** @var BaseJobQuote $jobQuote */
        $jobQuote = new $job->quote_class;
        $job->refresh();
        $jobQuote->saveQuote($job);
    }

    /**
     * @return array
     */
    public static function piqData()
    {
        return include (Yii::getAlias('@data')) . '/piq.php';
    }

}