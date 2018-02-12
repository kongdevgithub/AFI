<?php

namespace app\models\query;

/**
 * DearSaleQuery
 */
class DearSaleQuery extends DearQuery
{
    /**
     * @var
     */
    public $model_name;

    /**
     * @param \yii\db\QueryBuilder $builder
     * @return \yii\db\Query|DearQuery|DearSaleQuery
     */
    public function prepare($builder)
    {
        if ($this->model_name !== null) {
            $this->andWhere(['model_name' => $this->model_name]);
        }
        return parent::prepare($builder);
    }

}