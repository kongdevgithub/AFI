<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * PrintManager
 */
class PrintManager extends Component
{

    /**
     * @return string
     */
    private static function tmpPath()
    {
        $path = Yii::$app->runtimePath . '/print-manager';
        FileHelper::createDirectory($path);
        return $path;
    }

    /**
     * @param string $spool
     */
    public static function printTestLabel($spool)
    {
        $filename = LabelManager::getTest();
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     */
    public static function printSystemLabel($spool)
    {
        foreach (['none', 'new'] as $type) {
            $filename = LabelManager::getSystem($type);
            PrintSpool::spool($spool, $filename);
            unlink($filename);
        }
    }

    /**
     * @param string $spool
     */
    public static function printTestPdf($spool)
    {
        $filename = static::tmpPath() . '/test.pdf';
        PdfManager::getTest()->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Job $job
     */
    public static function printJobQuote($spool, $job)
    {
        $filename = static::tmpPath() . '/job-quote-' . $job->vid . '.pdf';
        PdfManager::getJobQuote($job)->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Job $job
     * @param string|null $item_types
     */
    public static function printJobProduction($spool, $job, $item_types = null)
    {
        if (!is_array($item_types)) $item_types = [$item_types];
        $filename = static::tmpPath() . '/job-production-' . $job->vid . ($item_types ? '-' . implode('-', $item_types) : '') . '.pdf';
        PdfManager::getJobProduction($job, implode(',', $item_types))->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }


    /**
     * @param string $spool
     * @param \app\models\Item $item
     */
    public static function printItemProduction($spool, $item)
    {
        $filename = static::tmpPath() . '/item-production-' . $item->id . '.pdf';
        PdfManager::getItemProduction($item)->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Item $item
     */
    public static function printItemLabel($spool, $item)
    {
        $filename = LabelManager::getItem($item);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Item $item
     */
    public static function printItemArtwork($spool, $item)
    {
        if ($item->artwork) {

            $localFile = Yii::$app->runtimePath . '/attachment/' . $item->artwork->getFileSrc();
            if (!file_exists($localFile)) {
                FileHelper::createDirectory(dirname($localFile));
                if (!Yii::$app->s3->get($item->artwork->getFileSrc(), $localFile)) {
                    return;
                }
            }
            PrintSpool::spool($spool, $localFile);
        }
    }

    /**
     * @param string $spool
     * @param \app\models\Package $package
     */
    public static function printPackagePdf($spool, $package)
    {
        $filename = static::tmpPath() . '/package-' . $package->id . '.pdf';
        PdfManager::getPackage($package)->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Package $package
     */
    public static function printPackageLabel($spool, $package)
    {
        //for ($i = 1; $i <= $package->cartons; $i++) {
        $filename = LabelManager::getPackage($package, [
            //        'carton' => $i,
        ]);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
        //}
    }

    /**
     * @param string $spool
     * @param \app\models\Pickup $pickup
     */
    public static function printPickupPdf($spool, $pickup)
    {
        $filename = static::tmpPath() . '/pickup-' . $pickup->id . '.pdf';
        PdfManager::getPickup($pickup)->saveAs($filename);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }

    /**
     * @param string $spool
     * @param \app\models\Component $component
     */
    public static function printComponentLabel($spool, $component)
    {
        $filename = LabelManager::getComponent($component);
        PrintSpool::spool($spool, $filename);
        unlink($filename);
    }
}
