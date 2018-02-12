<?php

namespace app\models\search;

use app\models\query\FeedbackQuery;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Feedback;

/**
 * FeedbackSearch represents the model behind the search form about `app\models\Feedback`.
 */
class FeedbackSearch extends Feedback
{

    public $requires_followup;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'followup_at', 'contact_id', 'created_at', 'updated_at', 'requires_followup'], 'integer'],
            [['score'], 'safe'],
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
        $query = Feedback::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'followup_at' => $this->followup_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if ($this->requires_followup) {
            $query->andWhere(['followup_at' => null]);
        }
        $query->andFilterOperator('score', $this->score);

        return $dataProvider;
    }


}
