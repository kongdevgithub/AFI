<?php
/**
 * /app/src/../runtime/giiant/e0080b9d6ffa35acb85312bf99a557f2
 *
 * @package default
 */


namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Carrier;

/**
 * CarrierSearch represents the model behind the search form about `app\models\Carrier`.
 */
class CarrierSearch extends Carrier
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
            [['name', 'my_freight_code','cope_freight_code'], 'safe'],
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
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Carrier::find()
            ->notDeleted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'my_freight_code', $this->my_freight_code])
            ->andFilterWhere(['like', 'cope_freight_code', $this->cope_freight_code]);

        return $dataProvider;
    }


}
