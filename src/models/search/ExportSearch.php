<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/e0080b9d6ffa35acb85312bf99a557f2
 *
 * @package default
 */


namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Export;

/**
 * ExportSearch represents the model behind the search form about `app\models\Export`.
 */
class ExportSearch extends Export
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'total_rows', 'created_at', 'updated_at'], 'integer'],
            [['model_name', 'model_params', 'status'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Export::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total_rows' => $this->total_rows,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status]);

        if ($this->model_name) {
            if (is_array($this->model_name)) {
                $query->andFilterWhere(['in', 'model_name', $this->model_name]);
            } else {
                $query->andFilterWhere(['like', 'model_name', $this->model_name]);
            }
        }

        return $dataProvider;
    }


}
