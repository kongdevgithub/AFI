<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "item_type".
 *
 * @mixin LinkBehavior
 *
 */
class ItemType extends base\ItemType
{
    /**
     *
     */
    const ITEM_TYPE_PRINT = 100;
    /**
     *
     */
    const ITEM_TYPE_FABRICATION = 200;
    /**
     *
     */
    const ITEM_TYPE_HARDWARE = 300;
    /**
     *
     */
    const ITEM_TYPE_INSTALLATION = 400;
    /**
     *
     */
    const ITEM_TYPE_EM_HARDWARE = 10002;
    /**
     *
     */
    const ITEM_TYPE_EM_PRINT = 10003;
    /**
     *
     */
    const ITEM_TYPE_PREBUILD = 10004;

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
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'quote_class', 'color', 'virtual', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'quote_class', 'color', 'virtual', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'quote_class', 'color', 'virtual', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getDescription($item)
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public static function getDropdownOpts()
    {
        return ArrayHelper::map(ItemType::find()->notDeleted()->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->color === null)) {
            $this->color = Helper::stringToColor(md5(uniqid()));
        }
        return $this;
    }

}
