<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductToOption;

/**
 * ProductToOptionSearch represents the model behind the search form about `app\models\ProductToOption`.
 */
class ProductToOptionSearch extends ProductToOption
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
            [['id', 'product_id', 'item_id', 'option_id', 'product_type_to_option_id', 'sort_order'], 'integer'],
            [['value', 'quote_class', 'quote_label'], 'safe'],
            [['quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost'], 'number'],
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
        $query = ProductToOption::find()->notDeleted();

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
            'product_id' => $this->product_id,
            'item_id' => $this->item_id,
            'option_id' => $this->option_id,
            'product_type_to_option_id' => $this->product_type_to_option_id,
            'sort_order' => $this->sort_order,
            'quote_unit_cost' => $this->quote_unit_cost,
            'quote_quantity' => $this->quote_quantity,
            'quote_total_cost' => $this->quote_total_cost,
            'quote_make_ready_cost' => $this->quote_make_ready_cost,
            'quote_factor' => $this->quote_factor,
            'quote_total_price' => $this->quote_total_price,
            'quote_minimum_cost' => $this->quote_minimum_cost,
        ]);

        $query->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'quote_class', $this->quote_class])
            ->andFilterWhere(['like', 'quote_label', $this->quote_label]);

        return $dataProvider;
    }

}