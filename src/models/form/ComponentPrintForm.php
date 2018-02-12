<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Component;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class ComponentPrintForm
 * @package app\models\form
 */
class ComponentPrintForm extends Model
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
        if (isset($values['ComponentPrintForm'])) {
            foreach ($values['ComponentPrintForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ComponentPrintForm']);
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
            $component = Component::findOne($id);
            if (!empty($this->print['component_label'])) {
                PrintManager::printComponentLabel($this->print_spool, $component);
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
        $print['component_label'] = Yii::t('app', 'Component Label');
        return $print;
    }
}