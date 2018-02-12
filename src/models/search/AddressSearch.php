<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Address;

/**
 * AddressSearch represents the model behind the search form about `app\models\Address`.
 */
class AddressSearch extends Address
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
            [['id','model_name', 'model_id', 'type', 'name', 'street', 'postcode', 'city', 'state', 'country'], 'safe'],
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
        $query = Address::find()->notDeleted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['IN', 'address.id', $this->id]);
            } else {
                $query->andFilterWhere(['address.id' => $this->id]);
            }
        }

        if ($this->type) {
            if (is_array($this->type)) {
                $query->andFilterWhere(['IN', 'address.type', $this->type]);
            } else {
                $query->andFilterWhere(['address.type' => $this->type]);
            }
        }

        if ($this->model_name) {
            if (is_array($this->model_name)) {
                $query->andFilterWhere(['IN', 'address.model_name', $this->model_name]);
            } else {
                $query->andFilterWhere(['address.model_name' => $this->model_name]);
            }
        }

        if ($this->model_id) {
            if (is_array($this->model_id)) {
                $query->andFilterWhere(['IN', 'address.model_id', $this->model_id]);
            } else {
                $query->andFilterWhere(['address.model_id' => $this->model_id]);
            }
        }

        $query->andFilterWhere([
            'postcode' => $this->postcode,
            'state' => $this->state,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'street', $this->street])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'country', $this->country]);

        return $dataProvider;
    }

}