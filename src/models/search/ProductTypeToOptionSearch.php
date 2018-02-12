<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductTypeToOption;

/**
 * ProductTypeToOptionSearch represents the model behind the search form about `app\models\ProductTypeToOption`.
 */
class ProductTypeToOptionSearch extends ProductTypeToOption
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
            [['id', 'product_type_id', 'product_type_to_item_type_id', 'option_id'], 'integer'],
            [['values'], 'safe'],
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
        $query = ProductTypeToOption::find()->notDeleted();

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
            'option_id' => $this->option_id,
        ]);

        $query->andFilterWhere(['like', 'values', $this->values]);

        return $dataProvider;
    }

}