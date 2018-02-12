<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\web\Application;

/**
 * PrintSpool
 */
class PrintSpool extends Component
{
    //
    ///**
    // * @var array files to be sent in the batch
    // * @see batchPrint()
    // */
    //public static $batchPrint = [];
    //
    ///**
    // * Send the emails in a batch
    // * @static
    // */
    //public static function batchPrint()
    //{
    //    foreach (static::$batchPrint as $spool => $files) {
    //        static::createCountFile($spool);
    //        foreach ($files as $file) {
    //            static::writeFile($spool, $file);
    //        }
    //    }
    //}

    /**
     * @param string $spool
     * @param string $file
     * @return bool
     */
    public static function writeFile($spool, $file)
    {
        $fileInfo = pathinfo($file);
        $spoolFile = date('Ymd_His') . '_' . number_format(microtime(true), 8, '.', '') . '_' . uniqid() . '_' . $fileInfo['basename'];
        copy($file, static::getPath($spool) . '/' . $spoolFile);
        static::setCountFile($spool);
        return true;
    }


    /**
     * Copies a file into the print spool folder
     *
     * @param string $spool
     * @param string $file
     * @return bool
     */
    public static function spool($spool, $file)
    {
        if (!$spool) {
            return false;
        }
        static::writeFile($spool, $file);
        //if (empty(static::$batchPrint)) {
        //    Yii::$app->on(Application::EVENT_AFTER_REQUEST, [static::className(), 'batchPrint']);
        //}
        //static::$batchPrint[$spool][] = $file;
        return true;
    }

    /**
     * @param $spool
     * @return string
     */
    public static function setCountFile($spool)
    {
        $path = static::getPath($spool);
        $files = FileHelper::findFiles($path);
        $contents = $files ? 'files' : '';
        $countFile = Yii::getAlias('@print-spool') . '/count/' . $spool . '/index.html';
        file_put_contents($countFile, $contents);
        return $contents;
    }

    /**
     * @param $spool
     * @return string
     */
    public static function getPath($spool)
    {
        $path = Yii::getAlias('@print-spool') . '/spool/' . $spool;
        FileHelper::createDirectory($path);
        FileHelper::createDirectory(Yii::getAlias('@print-spool') . '/count/' . $spool);
        return $path;
    }

    /**
     * @return array
     */
    public static function optsSpool()
    {
        return [
            'SEWING' => Yii::t('app', 'Sewing'),
            'DESPATCH' => Yii::t('app', 'Despatch'),
            'DESPATCH2' => Yii::t('app', 'Despatch2'),
            'SLS' => Yii::t('app', 'SLS'),
            'MOBILE' => Yii::t('app', 'Mobile'),
            //'FABRICATION' => Yii::t('app', 'Fabrication'),
            'PRINTFLOOR' => Yii::t('app', 'Print Floor'),
        ];
    }
}
