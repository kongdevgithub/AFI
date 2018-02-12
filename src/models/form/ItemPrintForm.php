<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Item;
use Yii;
use yii\base\Model;

/**
 * Class ItemPrintForm
 * @package app\models\form
 */
class ItemPrintForm extends Model
{

    /**
     * @var Item
     */
    public $item;

    /**
     * @var string
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print = [];

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var array
     */
    public $item_types = [];

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
            [['print_spool', 'print', 'items', 'item_types'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['ItemPrintForm'])) {
            foreach ($values['ItemPrintForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ItemPrintForm']);
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
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);
        if (!empty($this->print['item_production'])) {
            PrintManager::printItemProduction($this->print_spool, $this->item);
        }
        if (!empty($this->print['item_label'])) {
            PrintManager::printItemLabel($this->print_spool, $this->item);
        }

        return true;
    }

    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['item_production'] = Yii::t('app', 'Item Production');
        $print['item_label'] = Yii::t('app', 'Item Label');
        return $print;
    }

}