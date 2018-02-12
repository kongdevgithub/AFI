<?php

namespace app\modules\goldoc\models\query;

/**
 * This is the ActiveQuery class for [[\app\modules\goldoc\models\Sponsor]].
 *
 * @see \app\modules\goldoc\models\Sponsor
 */
class SponsorQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Sponsor[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\Sponsor|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}