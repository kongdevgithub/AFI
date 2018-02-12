<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form about `app\models\Product`.
 */
class ProductSearch extends Product
{

    public $job__status;

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
            [['id', 'job_id', 'name', 'status', 'product_type_id', 'job__status'], 'safe'],
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
        $query = Product::find();

        $query->joinWith('job');

        $query->andWhere('product.deleted_at IS NULL');
        $query->andWhere('job.deleted_at IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->status = null;

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'product.name', $this->name]);

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['in', 'product.id', $this->id]);
            } else {
                $query->andFilterWhere(['product.id' => $this->id]);
            }
        }

        if ($this->job_id) {
            if (is_array($this->job_id)) {
                $query->andFilterWhere(['in', 'product.job_id', $this->job_id]);
            } else {
                $query->andFilterWhere(['product.job_id' => $this->job_id]);
            }
        }

        if ($this->product_type_id) {
            if (is_array($this->product_type_id)) {
                $query->andFilterWhere(['in', 'product.product_type_id', $this->product_type_id]);
            } else {
                $query->andFilterWhere(['product.product_type_id' => $this->product_type_id]);
            }
        }

        if ($this->job__status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'job.status', $this->job__status]);
            } else {
                $query->andFilterWhere(['job.status' => $this->job__status]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'product.status', $this->status]);
            } else {
                $query->andFilterWhere(['product.status' => $this->status]);
            }
        }

        //debug($query->createCommand()->getRawSql()); die;

        return $dataProvider;
    }

}