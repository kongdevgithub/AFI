<?php

namespace app\modules\goldoc\models\query;

use cornernote\softdelete\SoftDeleteQueryBehavior;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\modules\goldoc\models\Product]].
 *
 * @see \app\modules\goldoc\models\Product
 *
 * @mixin SoftDeleteQueryBehavior
 */
class ProductQuery extends ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            SoftDeleteQueryBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Product[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Product|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}