<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\Csv;
use app\modules\goldoc\models\SignageFa;
use app\modules\goldoc\models\SignageFaToVenue;
use app\modules\goldoc\models\SignageWayfinding;
use app\modules\goldoc\models\Venue;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Class GoldocImportSignageController
 * @package app\commands
 */
class GoldocImportSignageController extends Controller
{

    /**
     * @return int
     */
    public function actionFa()
    {
        $this->stdout("Importing Signage FA\n");

        $data = Csv::csvToArray(Yii::getAlias('@data/goldoc/FA_Signage_sample_1.csv'));
        $count = count($data);
        foreach ($data as $k => $row) {
            /**
             * CODE
             * COMMENT
             * SIGN TEXT
             * GOLDOC PRODUCT ALLOCATED
             * MATERIAL
             * WIDTH (in m)
             * HEIGHT (in m)
             * FIXING
             * BEL
             * BLB
             * CCV
             * CAP
             * CSL
             * STA
             * CSW
             * CSB
             * COL
             * CSC
             * CUR
             * GAC
             * GCE
             * GHC
             * NER
             * OXN
             * OXB
             * OXS
             * OXT
             * QSV
             * ROB
             * SBP
             * TEN
             * AYC
             * BND
             * GTC
             * GUS - P
             * GUS-T
             * CBS
             * MHB
             * OBD
             * PSO
             * RIS
             * RBS
             * SHC - 1 GY
             * SHC 2 -WR
             * TSS - B
             * TSS - R
             * TVS
             * UAC
             * IBC
             * MPC
             * BNE
             * CNS
             * OOL
             * TSV
             * RVC
             * RVT
             * CGV (TVT)
             * CGV (TFT)
             * CGV (TBS)
             * CFH
             * CMH
             * CTO
             * GFH
             * TMH
             * TTO
             * MTM
             * VSP
             * GHQ
             * FDC
             * FDN
             * BDB
             * BDR
             */
            $this->stdout(CommandStats::stats($k + 1, $count) . "\n");

            $signageFa = new SignageFa();
            $signageFa->code = $row['CODE'];
            $signageFa->comment = $row['COMMENT'];
            $signageFa->goldoc_product_allocated = $row['GOLDOC PRODUCT ALLOCATED'];
            $signageFa->sign_text = $row['SIGN TEXT'];
            $signageFa->material = $row['MATERIAL'];
            $signageFa->width = $row['WIDTH (in m)'];
            $signageFa->height = $row['HEIGHT(in m)'];
            $signageFa->fixing = $row['FIXING'];
            if (!$signageFa->save(false)) {
                print_r($signageFa->errors);
            }

            $venues = [
                'BEL',
                'BLB',
                'CCV',
                'CAP',
                'CSL',
                'STA',
                'CSW',
                'CSB',
                'COL',
                'CSC',
                'CUR',
                'GAC',
                'GCE',
                'GHC',
                'NER',
                'OXN',
                'OXB',
                'OXS',
                'OXT',
                'QSV',
                'ROB',
                'SBP',
                'TEN',
                'AYC',
                'BND',
                'GTC',
                'GUS - P',
                'GUS-T',
                'CBS',
                'MHB',
                'OBD',
                'PSO',
                'RIS',
                'RBS',
                'SHC - 1 GY',
                'SHC 2 -WR',
                'TSS - B',
                'TSS - R',
                'TVS',
                'UAC',
                'IBC',
                'MPC',
                'BNE',
                'CNS',
                'OOL',
                'TSV',
                'RVC',
                'RVT',
                'CGV (TVT)',
                'CGV (TFT)',
                'CGV (TBS)',
                'CFH',
                'CMH',
                'CTO',
                'GFH',
                'TMH',
                'TTO',
                'MTM',
                'VSP',
                'GHQ',
                'FDC',
                'FDN',
                'BDB',
                'BDR',
            ];
            foreach ($venues as $venueCode) {
                if (!empty($row[$venueCode]) > 0) {
                    $venue = Venue::findOne(['code' => $venueCode]);
                    if (!$venue) {
                        $venue = new Venue();
                        $venue->code = $venueCode;
                        $venue->name = $venueCode;
                        $venue->save(false);
                    }
                    $signageFaToVenue = new SignageFaToVenue();
                    $signageFaToVenue->signage_fa_id = $signageFa->id;
                    $signageFaToVenue->venue_id = $venue->id;
                    $signageFaToVenue->quantity = $row[$venueCode];
                    $signageFaToVenue->save();
                }
            }

        }

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @return int
     */
    public function actionWayfinding()
    {
        $this->stdout("Importing Signage Wayfinding\n");

        $data = Csv::csvToArray(Yii::getAlias('@data/goldoc/Wayfinding_Schedule_sample_1.csv'));
        $count = count($data);
        foreach ($data as $k => $row) {
            /**
             * Batch
             * Quantity
             * Sign ID
             * Sign Code
             * Level
             * Message Side 1
             * Message Side 2
             * Fixing
             * Notes
             */
            $this->stdout(CommandStats::stats($k + 1, $count) . "\n");

            $signageWayfinding = new SignageWayfinding();
            $signageWayfinding->batch = $row['Batch'];
            $signageWayfinding->quantity = $row['Quantity'];
            $signageWayfinding->sign_id = $row['Sign ID'];
            $signageWayfinding->sign_code = $row['Sign Code'];
            $signageWayfinding->level = $row['Level'];
            $signageWayfinding->message_side_1 = $row['Message Side 1'];
            $signageWayfinding->message_side_2 = $row['Message Side 2'];
            $signageWayfinding->fixing = $row['Fixing'];
            $signageWayfinding->notes = $row['Notes'];
            if (!$signageWayfinding->save(false)) {
                print_r($signageWayfinding->errors);
            }

        }

        return self::EXIT_CODE_NORMAL;
    }

}