<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "note".
 *
 * @mixin LinkBehavior
 *
 */
class Note extends base\Note
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
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

    /**
     * @param array $attributes
     * @return Note|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $note = new Note();
        $note->loadDefaultValues();
        $note->attributes = $this->attributes;
        $note->id = null;
        $note->model_name = $attributes['Note']['model_name'];
        $note->model_id = $attributes['Note']['model_id'];
        $allowedAttributes = [
        ];
        if (!empty($attributes['Note'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Note'])) {
                    $note->$attribute = $attributes['Note'][$attribute];
                }
            }
        }
        if (!$note->save()) {
            throw new Exception('cannot copy Note-' . $this->id . ': ' . Helper::getErrorString($note));
        }
        return $note;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->model_name === null)) {
            $this->model_name = Yii::$app->className();
        }
        if ((!$skipIfSet || $this->model_id === null)) {
            $this->model_id = 0;
        }
        return $this;
    }

}
