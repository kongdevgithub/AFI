<?php

namespace app\models\form;

use app\components\EmailManager;
use app\components\PrintManager;
use app\models\Pickup;
use Yii;
use yii\base\Model;

/**
 * Class PickupProgressForm
 * @package app\models\form
 *
 */
class PickupProgressForm extends Model
{

    /**
     * @var
     */
    public $print_spool;

    /**
     * @var array
     */
    public $print;

    /**
     * @var int[]
     */
    public $ids;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $new_status;

    /**
     * @var bool
     */
    public $send_email = true;

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
            [['new_status'], 'required'],
            [['print_spool', 'print', 'send_email'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PickupProgressForm'])) {
            foreach ($values['PickupProgressForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PickupProgressForm']);
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
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        Yii::$app->user->identity->setEavAttribute('print_spool', $this->print_spool);

        $emailPickups = [];
        foreach ($this->ids as $pickup_id) {
            $pickup = Pickup::findOne($pickup_id);
            if ($pickup) {
                if (!$pickup->emailed_at && $this->send_email && $this->new_status == 'pickup/collected') {
                    $pickup->emailed_at = time();
                    $emailPickups[] = $pickup;
                }
                if (!$this->processPickup($pickup)) {
                    $transaction->rollBack();
                    return false;
                }
                $this->printPickup($pickup);
            }
        }

        if ($emailPickups) {
            EmailManager::sendPickupCollected($emailPickups);
        }

        $transaction->commit();
        return true;
    }

    /**
     * @param Pickup $pickup
     * @return bool
     */
    protected function processPickup($pickup)
    {
        if ($pickup->status == $this->status) {
            $pickup->send_email = false; // send email in bulk
            $pickup->status = $this->new_status;
            if (!$pickup->save(false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Pickup $pickup
     */
    protected function printPickup($pickup)
    {
        if (!empty($this->print['pickup_pdf'])) {
            PrintManager::printPickupPdf($this->print_spool, $pickup);
        }
    }

    /**
     * @return array
     */
    public function optsPrint()
    {
        $print = [];
        $print['pickup_pdf'] = Yii::t('app', 'Pickup PDF');
        return $print;
    }

}