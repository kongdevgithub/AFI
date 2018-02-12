<?php

namespace app\models\form;

use app\components\PrintManager;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class PackageSystemPrintForm
 * @package app\models\form
 */
class PackageSystemPrintForm extends Model
{

    /**
     * @var
     */
    public $print_spool;

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
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PackageSystemPrintForm'])) {
            foreach ($values['PackageSystemPrintForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PackageSystemPrintForm']);
        }
        if (!empty($values['print'])) {
            $print = [];
            foreach ($values['print'] as $k => $v) {
                $print[$v] = $v;
            }
            $values['print'] = $print;
        }
        parent::setAttributes($values, $safeOnly);
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
        if ($this->print_system_barcode) {
            PrintManager::printTestLabel($this->print_spool);
        }

        return true;
    }

}