<?php

namespace app\models;

use app\components\GearmanManager;
use bedezign\yii2\audit\AuditTrailBehavior;
use mar\eav\behaviors\EavBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "export".
 *
 * @property string $gearman_process
 */
class Export extends base\Export
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors['eav'] = [
            'class' => EavBehavior::className(),
            'modelAlias' => static::className(),
            'eavAttributesList' => [
                'gearman_process' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
            ],
        ];
        $behaviors[] = TimestampBehavior::className();
        //$behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     *
     */
    public function spoolGearman()
    {
        if (!$this->spoolGearmanStatus()) {
            $this->gearman_process = GearmanManager::runExport($this->id);
            $this->save(false);
        }
    }

    /**
     * @return bool
     */
    public function spoolGearmanStatus()
    {
        if ($this->gearman_process) {
            $stat = GearmanManager::getBackgroundStatus(Yii::$app->gearmanExport, $this->gearman_process);
            if ($stat[0]) {
                return true;
            }
            $this->gearman_process = null;
            $this->save(false);
        }
        return false;
    }


}
