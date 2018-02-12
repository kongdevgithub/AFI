<?php

namespace app\models\query;

use cornernote\softdelete\SoftDeleteQueryBehavior;

/**
 * This is the ActiveQuery class for [[\app\models\CompanyRateOption]].
 *
 * @mixin SoftDeleteQueryBehavior
 *
 * @see \app\models\CompanyRateOption
 */
class CompanyRateOptionQuery extends \yii\db\ActiveQuery
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
     * @return \app\models\CompanyRateOption[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\CompanyRateOption|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}