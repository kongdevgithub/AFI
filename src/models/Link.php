<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "link".
 */
class Link extends base\Link
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
     * @return Link|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $link = new Link();
        $link->loadDefaultValues();
        $link->attributes = $this->attributes;
        $link->id = null;
        $link->model_name = $attributes['Link']['model_name'];
        $link->model_id = $attributes['Link']['model_id'];
        $allowedAttributes = [
        ];
        if (!empty($attributes['Link'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Link'])) {
                    $link->$attribute = $attributes['Link'][$attribute];
                }
            }
        }
        if (!$link->save()) {
            throw new Exception('cannot copy Link-' . $this->id . ': ' . Helper::getErrorString($link));
        }
        return $link;
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
