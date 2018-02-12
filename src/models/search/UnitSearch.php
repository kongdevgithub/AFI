<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Unit;

/**
 * UnitSearch represents the model behind the search form about `app\models\Unit`.
 */
class UnitSearch extends Unit
{

    public $item__status;
    public $product__status;
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
            [['id', 'item_id', 'package_id', 'status', 'quantity', 'item__status', 'product__status', 'job__status'], 'safe'],
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
        $query = Unit::find();
        $query->andWhere('unit.deleted_at IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['in', 'unit.id', $this->id]);
            } else {
                $query->andFilterWhere(['unit.id' => $this->id]);
            }
        }

        if ($this->item_id) {
            if (is_array($this->item_id)) {
                $query->andFilterWhere(['in', 'unit.item_id', $this->item_id]);
            } else {
                $query->andFilterWhere(['unit.item_id' => $this->item_id]);
            }
        }

        if ($this->package_id) {
            if (is_array($this->package_id)) {
                $query->andFilterWhere(['in', 'unit.package_id', $this->package_id]);
            } else {
                $query->andFilterWhere(['unit.package_id' => $this->package_id]);
            }
        }

        if ($this->job__status) {
            $query->joinWith('item.product.job');
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'job.status', $this->job__status]);
            } else {
                $query->andFilterWhere(['job.status' => $this->job__status]);
            }
        }

        if ($this->product__status) {
            $query->joinWith('item.product');
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'product.status', $this->product__status]);
            } else {
                $query->andFilterWhere(['product.status' => $this->product__status]);
            }
        }

        if ($this->item__status) {
            $query->joinWith('item');
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'item.status', $this->item__status]);
            } else {
                $query->andFilterWhere(['item.status' => $this->item__status]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'unit.status', $this->status]);
            } else {
                $query->andFilterWhere(['unit.status' => $this->status]);
            }
        }

        if ($this->quantity) {
            $query->andFilterCompare('unit.quantity', $this->quantity);
        }

        //debug($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }

}