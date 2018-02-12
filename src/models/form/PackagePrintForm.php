<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Package;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class PackagePrintForm
 * @package app\models\form
 */
class PackagePrintForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print;

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
            [['print_spool', 'print'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PackagePrintForm'])) {
            foreach ($values['PackagePrintForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PackagePrintForm']);
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
        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            if (!empty($this->print['package_pdf'])) {
                PrintManager::printPackagePdf($this->print_spool, $package);
            }
            if (!empty($this->print['package_label'])) {
                PrintManager::printPackageLabel($this->print_spool, $package);
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['package_pdf'] = Yii::t('app', 'Package PDF');
        $print['package_label'] = Yii::t('app', 'Package Label');
        return $print;
    }
}