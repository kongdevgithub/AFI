<?php

namespace app\models\query;

/**
 * DearProductQuery
 */
class DearProductQuery extends DearQuery
{
    /**
     * @var
     */
    public $model_name;

    /**
     * @param \yii\db\QueryBuilder $builder
     * @return \yii\db\Query|DearQuery|DearProductQuery
     */
    public function prepare($builder)
    {
        if ($this->model_name !== null) {
            $this->andWhere(['model_name' => $this->model_name]);
        }
        return parent::prepare($builder);
    }

}