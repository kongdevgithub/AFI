<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductType;

/**
 * ProductTypeSearch represents the model behind the search form about `app\models\ProductType`.
 */
class ProductTypeSearch extends ProductType
{

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass parent scenarios
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductType::find();
        $query->andWhere('product_type.deleted_at IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['sort_order' => SORT_ASC, 'name' => SORT_ASC]],
            'pagination' => [
                'pageSize' => 1000,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        if ($this->parent_id) {
            $query->andFilterWhere([
                'parent_id' => $this->parent_id,
            ]);
        }

        if ($this->parent_id === 0) {
            $query->andWhere([
                'parent_id' => null,
            ]);
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}