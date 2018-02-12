<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductToAddress;

/**
 * ProductToAddressSearch represents the model behind the search form about `app\models\ProductToAddress`.
 */
class ProductToAddressSearch extends ProductToAddress
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'address_id', 'product_id', 'quantity'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
// bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = ProductToAddress::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'address_id' => $this->address_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
        ]);

        return $dataProvider;
    }
}