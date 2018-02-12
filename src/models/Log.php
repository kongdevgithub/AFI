<?php

namespace app\models;

use app\components\GearmanManager;
use app\components\Helper;
use bedezign\yii2\audit\Audit;
use bedezign\yii2\audit\models\AuditEntry;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "log".
 */
class Log extends base\Log
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => TimestampBehavior::className(),
            'updatedAtAttribute' => null,
        ];
        return $behaviors;
    }


    /**
     * @param string $message
     * @param ActiveRecord|string $model_name
     * @param int|string $model_id
     * @return static
     * @throws Exception
     */
    public static function log($message, $model_name = null, $model_id = null)
    {
        if ($model_name instanceof ActiveRecord) {
            $model_id = $model_name->primaryKey;
            $model_name = $model_name->className();
        }
        $log = new Log;
        $log->message = $message;
        $log->created_by = Yii::$app instanceof \yii\web\Application ? Yii::$app->user->id : 0;
        $log->audit_entry_id = static::getAuditEntryId();
        $log->model_name = $model_name;
        $log->model_id = $model_id;
        if (!$log->save()) {
            throw new Exception(Helper::getErrorString($log));
            //return false;
        }
        //GearmanManager::runLogSlack($log->id);
        return $log;
    }

    /**
     * @return int|null|string
     */
    private static function getAuditEntryId()
    {
        /** @var Audit $audit */
        $audit = Yii::$app->getModule('audit');
        $entry = $audit->getEntry(true);
        return $entry ? $entry->id : null;
    }

    /**
     * @return array
     */
    public function getAuditTrails()
    {
        $trails = [];
        $ignoreFields = [
            'updated_at',
            'created_at',
            'updated_by',
            'created_by',
            'deleted_by',
            'quote_generated',
            'hub_spot_pulled',
            'hub_spot_pushed',
        ];
        $entry = AuditEntry::findOne($this->audit_entry_id);
        if ($entry) {
            foreach ($entry->trails as $trail) {
                if (in_array($trail->field, $ignoreFields)) {
                    continue;
                }
                if ($trail->field == 'status' && !$trail->new_value) {
                    continue;
                }
                if ($trail->action == 'DELETE' || ($trail->action == 'UPDATE' && $trail->field == 'deleted_at' && $trail->new_value)) {
                    $field = str_replace('app\\models\\', '', $trail->model) . '.' . $trail->model_id;
                    $trails[] = Html::tag('strike', $field);
                } else {
                    $field = str_replace('app\\models\\', '', $trail->model) . '.' . $trail->model_id . '.' . $trail->field;
                    $new = Html::tag('strong', Html::encode(Helper::getAuditTrailValue($trail->model, $trail->field, $trail->new_value)));
                    $old = Html::tag('strike', Html::encode(Helper::getAuditTrailValue($trail->model, $trail->field, $trail->old_value)));
                    $trails[] = trim($field . ' = ' . $new . ' ' . $old);
                }
            }
        }
        return $trails;
    }

}
