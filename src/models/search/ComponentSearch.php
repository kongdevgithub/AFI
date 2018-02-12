<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Component;

/**
 * ComponentSearch represents the model behind the search form about `app\models\Component`.
 */
class ComponentSearch extends Component
{

    public $keywords;

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
            [['id', 'component_type_id'], 'integer'],
            [['code', 'name', 'brand', 'status', 'quote_class', 'unit_of_measure', 'unit_weight', 'quantity_factor', 'keywords'], 'safe'],
            [['make_ready_cost', 'unit_cost', 'minimum_cost'], 'number'],
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
        $query = Component::find()->notDeleted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['code' => SORT_ASC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'component_type_id' => $this->component_type_id,
            'make_ready_cost' => $this->make_ready_cost,
            'unit_cost' => $this->unit_cost,
            'minimum_cost' => $this->minimum_cost,
            'status' => $this->status,
            'quote_class' => $this->quote_class,
            'unit_weight' => $this->unit_weight,
            'unit_of_measure' => $this->unit_of_measure,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'brand', $this->brand])
            ->andFilterWhere(['like', 'quantity_factor', $this->quantity_factor]);


        if ($this->keywords) {
            $query->andFilterWhere([
                'or',
                ['like', 'code', $this->keywords],
                ['like', 'name', $this->keywords],
            ]);
        }

        return $dataProvider;
    }

}