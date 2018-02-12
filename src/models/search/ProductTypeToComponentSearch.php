<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductTypeToComponent;

/**
 * ProductTypeToComponentSearch represents the model behind the search form about `app\models\ProductTypeToComponent`.
 */
class ProductTypeToComponentSearch extends ProductTypeToComponent
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
            [['id', 'product_type_id', 'product_type_to_item_type_id', 'component_id', 'quantity', 'sort_order'], 'integer'],
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
        $query = ProductTypeToComponent::find()->notDeleted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
            'product_type_id' => $this->product_type_id,
            'product_type_to_item_type_id' => $this->product_type_to_item_type_id,
            'component_id' => $this->component_id,
            'quantity' => $this->quantity,
            'sort_order' => $this->sort_order,
        ]);

        return $dataProvider;
    }

}