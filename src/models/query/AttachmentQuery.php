<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Attachment]].
 *
 * @see \app\models\Attachment
 */
class AttachmentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Attachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Attachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}