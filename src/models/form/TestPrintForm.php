<?php

namespace app\models\form;

use app\components\PrintManager;
use app\components\PrintSpool;
use app\models\Carrier;
use app\models\Package;
use app\models\Pickup;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class TestPrintForm
 * @package app\models\form
 */
class TestPrintForm extends Model
{

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var int
     */
    public $print_barcode;

    /**
     * @var int
     */
    public $print_system_barcode;

    /**
     * @var
     */
    public $print_pdf;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->print_spool = Yii::$app->user->identity->print_spool;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['print_spool'], 'required'],
            [['print_barcode', 'print_pdf','print_system_barcode'], 'integer'],
        ];
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);

        if ($this->print_pdf) {
            PrintManager::printTestPdf($this->print_spool);
        }
        if ($this->print_barcode) {
            PrintManager::printTestLabel($this->print_spool);
        }
        if ($this->print_system_barcode){
            PrintManager::printSystemLabel($this->print_spool);
        }

        return true;
    }

}