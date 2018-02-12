<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductTypeToItemType;

/**
 * ProductTypeToItemTypeSearch represents the model behind the search form about `app\models\ProductTypeToItemType`.
 */
class ProductTypeToItemTypeSearch extends ProductTypeToItemType
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
            [['id', 'product_type_id', 'item_type_id'], 'integer'],
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
        $query = ProductTypeToItemType::find()->notDeleted();

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
            'item_type_id' => $this->item_type_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}