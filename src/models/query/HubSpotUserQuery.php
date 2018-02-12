<?php

namespace app\models\query;

/**
 * HubSpotUserQuery
 */
class HubSpotUserQuery extends HubSpotQuery
{
    /**
     * @var
     */
    public $model_name;

    /**
     * @param \yii\db\QueryBuilder $builder
     * @return $this|\yii\db\Query
     */
    public function prepare($builder)
    {
        if ($this->model_name !== null) {
            $this->andWhere(['model_name' => $this->model_name]);
        }
        return parent::prepare($builder);
    }

}