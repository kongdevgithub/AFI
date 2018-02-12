<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification".
 */
class Notification extends base\Notification
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //$behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @param string $type
     * @param string $title
     * @param string $body
     * @param ActiveRecord|string $model_name
     * @param int|string $model_id
     * @return static
     * @throws Exception
     */
    public static function add($type, $title, $body = null, $model_name = null, $model_id = null)
    {
        if ($model_name instanceof ActiveRecord) {
            $model_id = $model_name->primaryKey;
            $model_name = $model_name->className();
        }
        $notification = new Notification;
        $notification->type = $type;
        $notification->title = $title;
        $notification->body = $body;
        $notification->model_name = $model_name;
        $notification->model_id = $model_id;
        if (!$notification->save()) {
            throw new Exception(Helper::getErrorString($notification));
            //return false;
        }
        return $notification;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (Yii::$app instanceof \yii\web\Application) {
            $this->updated_by = Yii::$app->user->id;
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Helper::clearRelatedCache($this);
        parent::afterSave($insert, $changedAttributes);
    }

}
