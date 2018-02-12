<?php

namespace app\modules\goldoc\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\goldoc\models\SignageFa;

/**
 * SignageFaSearch represents the model behind the search form about `app\modules\goldoc\models\SignageFa`.
 */
class SignageFaSearch extends SignageFa
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
            [['id'], 'integer'],
            [['code', 'comment', 'sign_text', 'goldoc_product_allocated', 'material', 'fixing'], 'safe'],
            [['width', 'height'], 'number'],
        ];
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
        $query = SignageFa::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'sign_text', $this->sign_text])
            ->andFilterWhere(['like', 'goldoc_product_allocated', $this->goldoc_product_allocated])
            ->andFilterWhere(['like', 'material', $this->material])
            ->andFilterWhere(['like', 'fixing', $this->fixing]);

        return $dataProvider;
    }


}
