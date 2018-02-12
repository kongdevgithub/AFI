<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * LabelManager
 */
class LabelManager extends Component
{

    /**
     * @param array $vars
     * @return bool
     */
    public static function getTest($vars = [])
    {
        return static::getLabel('test', ArrayHelper::merge([
            'barcode' => 'TEST LABEL',
            'barcode_title' => 'TEST LABEL',
            'text' => 'TEST LABEL',
        ], $vars));
    }

    /**
     * @param string $type
     * @return bool | string
     */
    public static function getSystem($type)
    {
        return static::getLabel('system', [
            'barcode' => 'package-' . strtolower($type),
            'barcode_title' => 'Package ' . ucfirst($type),
            'text' => 'Package ' . ucfirst($type),
        ]);
    }

    /**
     * @param \app\models\Item $item
     * @return bool
     */
    public static function getItem($item)
    {
        $vars = [
            'job_name' => implode(' | ', [
                $item->product->job->name,
                // $item->product->job->company->name, // remove requested by James
            ]),
            'item_name' => implode("\n", [
                $item->product->name,
                $item->name,
                $item->itemType->name . ': ' . $item->product->getDescription(['showName' => false, 'showDetails' => false, 'showItems' => false]),
            ]),
            'job_id' => 'job-' . $item->product->job->vid,
            'item_id' => 'item-' . $item->id,
        ];
        if ($item->artwork) {
            $vars['image_string'] = base64_encode(file_get_contents($item->artwork->getFileUrl('100x100')));
        } elseif ($item->product->productType) {
            $vars['image_string'] = base64_encode(file_get_contents($item->product->productType->getImageSrc()));
        }
        return static::getLabel('item', $vars);
    }


    /**
     * @param \app\models\Component $component
     * @return bool
     */
    public static function getComponent($component)
    {
        $vars = [
            'component_name' => $component->name,
            'component_code' => $component->code,
            'component_id' => 'component-' . $component->id,
        ];
        return static::getLabel('component', $vars);
    }

    /**
     * @param \app\models\Package $package
     * @param array $vars
     * @return bool
     */
    public static function getPackage($package, $vars = [])
    {
        $mainPackage = $package;
        if ($package->overflowPackage) {
            $mainPackage = $package->overflowPackage;
        }
        $job = $mainPackage->getFirstJob();
        return static::getLabel('package_large', ArrayHelper::merge([
            'package_id' => 'package-' . $package->id,
            'cartons' => $package->getCartonCountLabel(),
            'title' => $job ? $job->name : '',
            'address' => $package->address ? $package->address->getLabel("\n") : '',
        ], $vars));
    }

    /**
     * Generates a label
     *
     * @param $template
     * @param $vars array
     * @return bool | string
     */
    public static function getLabel($template, $vars = [])
    {
        $outputFile = Yii::$app->runtimePath . '/label/' . $template . '_' . md5(serialize($vars)) . '.label';
        $templateFile = Yii::getAlias('@app') . '/label/' . $template . '.label';
        if (!file_exists($templateFile)) {
            return false;
        }
        $vars = static::cleanVars($vars);
        $contents = file_get_contents($templateFile);
        $templateVars = array();
        foreach ($vars as $k => $v) {
            $templateVars['{{' . $k . '}}'] = $v;
        }
        $contents = strtr($contents, $templateVars);
        if (!file_exists(dirname($outputFile))) {
            FileHelper::createDirectory(dirname($outputFile));
        }
        if (!file_put_contents($outputFile, $contents)) {
            return false;
        }
        return $outputFile;
    }

    /**
     * @param array $vars
     * @return array
     */
    protected static function cleanVars($vars = array())
    {
        // set an empty image for item/unit barcodes with missing image
        if (!isset($vars['image_string']) || !$vars['image_string']) {
            $vars['image_string'] = base64_encode(file_get_contents(Yii::getAlias('@app') . '/label/empty.jpg'));
        }

        // fix delivery address because dymo is picky
        $text = '';
        if (isset($vars['text'])) {
            foreach (explode("\n", trim($vars['text'])) as $row) {
                $row = trim($row);
                if (!$row) {
                    $row = '.';
                }
                $text .= $row . "\n";
            }
        }
        $vars['text'] = $text;
        // send it back
        return $vars;
    }

}
