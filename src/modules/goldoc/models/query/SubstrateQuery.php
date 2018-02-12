<?php

namespace app\modules\goldoc\models\query;

/**
 * This is the ActiveQuery class for [[\app\modules\goldoc\models\Substrate]].
 *
 * @see \app\modules\goldoc\models\Substrate
 */
class SubstrateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Substrate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Substrate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}