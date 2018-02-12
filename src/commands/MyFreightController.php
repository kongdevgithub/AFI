<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\YdXml2Array;
use app\models\Contact;
use app\models\Pickup;
use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Class MyFreightController
 * @package app\commands
 */
class MyFreightController extends Controller
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->run('my-freight/process-consignments');
        return self::EXIT_CODE_NORMAL;
    }

    public function actionTest()
    {
        $this->processConsignment($localFile = Yii::$app->runtimePath . '/my-freight/AGU100007590_20170927115044_30ade457ba79d8dfed78ff8a798aa6f8.xml');
    }

    public function actionProcessConsignments()
    {
        $this->stdout("Downloading Consignments\n");

        $runtimePath = Yii::$app->runtimePath . '/my-freight';
        FileHelper::createDirectory($runtimePath);

        $ftp = ftp_connect('ftp.afibranding.com.au', 21, 30);
        ftp_login($ftp, 'myfreight@afibranding.com.au', '9q47TJT3h20979D');
        ftp_pasv($ftp, true);
        $files = ftp_nlist($ftp, '/');
        if ($files) {
            $count = count($files);
            foreach ($files as $k => $file) {
                $this->stdout(CommandStats::stats($k + 1, $count) . '- ' . $file);
                if (strpos($file, '.xml') === false) {
                    $this->stdout(' - not an xml file' . "\n");
                    continue;
                }
                $localFile = $runtimePath . '/' . $file;
                ftp_get($ftp, $localFile, $file, FTP_ASCII);
                $this->processConsignment($localFile);
                $this->stdout(' - cleanup');
                @ftp_mkdir($ftp, '/done');
                ftp_rename($ftp, $file, '/done/' . $file);
                //ftp_delete($ftp, $file);
                //unlink($localFile);

                $this->stdout("\n");
            }
        }

        //$contacts = Contact::find()->notDeleted()->all();
        //$count = count($contacts);
        //foreach ($contacts as $k => $contact) {
        //    echo CommandStats::stats($k + 1, $count);
        //    echo "\n";
        //}
        return self::EXIT_CODE_NORMAL;
    }

    private function processConsignment($localFile)
    {
        $xml = YdXml2Array::createArray(file_get_contents($localFile));
        if ($xml) {
            $pickupReference = trim($xml['Consignments']['Consignment']['Header']['Reference']);
            $consignmentNumber = trim($xml['Consignments']['Consignment']['Header']['ConsignmentNumber']);
            if ($pickupReference && $consignmentNumber) {
                $pickup = false;
                if (substr($pickupReference, 0, 7) == 'pickup-') {
                    $pickup = Pickup::findOne(substr($pickupReference, 7));
                }
                if ($pickup) {
                    if (!$pickup->carrier_ref) {
                        $this->stdout(' - updating pickup-' . $pickup->id . ' to carrier_ref=' . $consignmentNumber);
                        $pickup->carrier_ref = $consignmentNumber;
                        $pickup->save(false);
                    } else {
                        $this->stdout(' - pickup already has carrier_ref');
                    }
                } else {
                    $this->stdout(' - missing pickup ' . $pickupReference);
                }
            } else {
                $this->stdout(' - missing references');
            }
        } else {
            $this->stdout(' - bad xml');
        }
    }
}
