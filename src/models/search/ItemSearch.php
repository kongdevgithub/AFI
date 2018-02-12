<?php

namespace app\models\search;

use app\traits\DateSearchTrait;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Item;

/**
 * ItemSearch represents the model behind the search form about `app\models\Item`.
 */
class ItemSearch extends Item
{
    use DateSearchTrait;

    /**
     * @var bool
     */
    public $artwork;
    /**
     * @var bool|int
     */
    public $machine;

    public $product__status;
    public $product__prebuild_required;
    public $job__status;
    public $job__client_company_id;
    public $job__due_date;
    public $job__due_date_from;
    public $job__due_date_to;

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
            [['id', 'product_id', 'item_type_id', 'product_type_to_item_type_id', 'name', 'quote_class', 'due_date',
                'status', 'artwork', 'machine', 'product__status', 'job__status', 'product__prebuild_required', 'quantity',
                'job__client_company_id', 'job__due_date', 'job__due_date_from', 'job__due_date_to'], 'safe'],
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
        $query = Item::find();

        $query->andWhere('item.deleted_at IS NULL');
        $query->andWhere('product.deleted_at IS NULL');
        $query->andWhere('job.deleted_at IS NULL');
        $query->andWhere('product.fork_quantity_product_id IS NULL');

        $query->joinWith('product.job');

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

        $query->andFilterWhere([
            'item.product_type_to_item_type_id' => $this->product_type_to_item_type_id,
            'item.due_date' => $this->due_date,
            'item.quote_class' => $this->quote_class,
        ]);

        $query->andFilterCompare('item.quantity', $this->quantity);

        $query->andFilterWhere(['like', 'item.name', $this->name]);

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['in', 'item.id', $this->id]);
            } else {
                $query->andFilterWhere(['item.id' => $this->id]);
            }
        }

        if ($this->product_id) {
            if (is_array($this->product_id)) {
                $query->andFilterWhere(['in', 'item.product_id', $this->product_id]);
            } else {
                $query->andFilterWhere(['item.product_id' => $this->product_id]);
            }
        }

        if ($this->item_type_id) {
            if (is_array($this->item_type_id)) {
                $query->andFilterWhere(['in', 'item.item_type_id', $this->item_type_id]);
            } else {
                $query->andFilterWhere(['item.item_type_id' => $this->item_type_id]);
            }
        }

        if ($this->artwork !== null) {
            $query->leftJoin('attachment artwork', 'artwork.deleted_at IS NULL AND artwork.model_id = item.id AND artwork.model_name = :model_name', [
                ':model_name' => Item::className() . '-Artwork',
            ]);
            if ($this->artwork) {
                $query->andWhere('artwork.id IS NOT NULL');
            } else {
                $query->andWhere('artwork.id IS NULL');
            }
        }

        if ($this->machine !== null) {
            $query->joinWith('itemToMachines');
            if ($this->machine) {
                $query->andWhere(['item_to_machine.machine_id' => $this->machine]);
            } else {
                $query->andWhere('item_to_machine.id IS NULL');
            }
        }

        if ($this->job__status) {
            if (is_array($this->job__status)) {
                $query->andFilterWhere(['in', 'job.status', $this->job__status]);
            } else {
                $query->andFilterWhere(['job.status' => $this->job__status]);
            }
        }

        if ($this->product__status) {
            if (is_array($this->product__status)) {
                $query->andFilterWhere(['in', 'product.status', $this->product__status]);
            } else {
                $query->andFilterWhere(['product.status' => $this->product__status]);
            }
        }

        if ($this->product__prebuild_required) {
            if (is_array($this->product__prebuild_required)) {
                $query->andFilterWhere(['in', 'product.prebuild_required', $this->product__prebuild_required]);
            } else {
                $query->andFilterWhere(['product.prebuild_required' => $this->product__prebuild_required]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'item.status', $this->status]);
            } else {
                $query->andFilterWhere(['item.status' => $this->status]);
            }
        }

        if ($this->job__client_company_id) {
            $query->andFilterWhere(['job.company_id' => $this->job__client_company_id]);
        }


        if ($this->job__due_date) {
            $this->dateFilter($query, 'job.due_date', $this->job__due_date);
        }
        if ($this->job__due_date_from) {
            $query->andWhere(['>=', 'job.due_date', $this->job__due_date_from]);
        }
        if ($this->job__due_date_to) {
            $query->andWhere(['<=', 'job.due_date', $this->job__due_date_to]);
        }

        //debug($query->createCommand()->getRawSql());
        //die;

        return $dataProvider;
    }

}