<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "correction".
 *
 * @property User $user
 */
class Correction extends base\Correction
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        return $behaviors;
    }

    /**
     * @return array
     */
    public static function getChangedAlertStatusList()
    {
        return [
            'job/production' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/powdercoat' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/manufacture' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/fabrication' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/cut' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/light' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-fabrication/quality' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'item-print/design' => ['dan@afibranding.com.au'],
            'item-print/rip' => ['dan@afibranding.com.au'],
            'unit-print/printing' => ['dan@afibranding.com.au'],
            'unit-print/cutting' => ['dan@afibranding.com.au'],
            'unit-print/sewPending' => ['dan@afibranding.com.au', 'brett@afibranding.com.au'],
            'unit-print/sewing' => ['dan@afibranding.com.au', 'brett@afibranding.com.au'],
            'unit-print/quality' => ['dan@afibranding.com.au', 'brett@afibranding.com.au'],
            'unit-outsourcePrint/outsource' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-outsourcePrint/sewing' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-outsourceHardware/outsource' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-outsourceHardware/stockCheck' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-installation/outsource' => ['nick@afibranding.com.au', 'alanna@afibranding.com.au'],
            'unit-*/despatch' => ['james@afibranding.com.au'],
            'unit-*/packed' => ['james@afibranding.com.au'],
            'unit-*/prebuild' => ['brett@afibranding.com.au', 'dan@afibranding.com.au', 'nick@afibranding.com.au', 'alanna@afibranding.com.au'],
        ];
    }

    /**
     * @return array
     */
    public static function optsReason()
    {
        return [
            'none' => Yii::t('app', 'No Correction (no change from existing quote)'),
            'external' => Yii::t('app', 'External Correction (by client)'),
            'internal' => Yii::t('app', 'Internal Correction (by staff)'),
        ];
    }

    /**
     * @param $action
     * @param $reason
     * @param null $changes
     * @param null $model_name
     * @param null $model_id
     * @return Correction
     * @throws Exception
     */
    public static function add($action, $reason, $changes = null, $model_name = null, $model_id = null)
    {
        if ($model_name instanceof ActiveRecord) {
            $model_id = $model_name->primaryKey;
            $model_name = $model_name->className();
        }
        $correction = new Correction;
        $correction->action = $action;
        $correction->reason = $reason;
        $correction->changes = $changes;
        $correction->user_id = Yii::$app->user->id;
        $correction->model_name = $model_name;
        $correction->model_id = $model_id;
        if (!$correction->save()) {
            throw new Exception(Helper::getErrorString($correction));
            //return false;
        }
        return $correction;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
