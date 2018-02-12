<?php

namespace app\models\query;
use cornernote\softdelete\SoftDeleteQueryBehavior;

/**
 * This is the ActiveQuery class for [[\app\models\ComponentType]].
 *
 * @see \app\models\ComponentType
 * 
 * @mixin SoftDeleteQueryBehavior
 */
class ComponentTypeQuery extends \yii\db\ActiveQuery
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
     * @return \app\models\ComponentType[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\ComponentType|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}