<?php

namespace app\models\form;

use app\components\PrintManager;
use app\models\Job;
use Yii;
use yii\base\Model;

/**
 * Class JobPrintForm
 * @package app\models\form
 */
class JobPrintForm extends Model
{

    /**
     * @var Job
     */
    public $job;

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
        $this->item_types = Yii::$app->user->identity->job_print_item_types;
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
        if (isset($values['JobPrintForm'])) {
            foreach ($values['JobPrintForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['JobPrintForm']);
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

        Yii::$app->user->identity->job_print_item_types = $this->item_types;
        Yii::$app->user->identity->print_spool = $this->print_spool;
        Yii::$app->user->identity->save(false);

        if (!empty($this->print['job_production'])) {
            PrintManager::printJobProduction($this->print_spool, $this->job, $this->item_types);
        }

        if ($this->item_types) {
            foreach ($this->job->products as $product) {
                foreach ($product->items as $item) {
                    if (!in_array($item->item_type_id, $this->item_types)) continue;
                    if (!empty($this->print['item_production'])) {
                        PrintManager::printItemProduction($this->print_spool, $item);
                    }
                    if (!empty($this->print['item_artwork']) && $item->artwork) {
                        PrintManager::printItemArtwork($this->print_spool, $item);
                    }
                    if (!empty($this->print['item_label']) && !empty($this->items[$item->id])) {
                        for ($i = 0; $i < $this->items[$item->id]; $i++) {
                            PrintManager::printItemLabel($this->print_spool, $item);
                        }
                    }
                }
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
        $print['job_production'] = Yii::t('app', 'Job Production');
        $print['item_production'] = Yii::t('app', 'Item Production');
        $print['item_artwork'] = Yii::t('app', 'Item Artwork');
        $print['item_label'] = Yii::t('app', 'Item Label');
        return $print;
    }

}