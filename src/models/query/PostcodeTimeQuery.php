<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\PostcodeTime]].
 *
 * @see \app\models\PostcodeTime
 */
class PostcodeTimeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\PostcodeTime[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\PostcodeTime|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}