<?php

namespace app\modules\goldoc\models\search;

use app\modules\goldoc\models\query\ProductQuery;
use yii\data\ActiveDataProvider;
use app\modules\goldoc\models\Product;

/**
 * ProductSearch represents the model behind the search form about `app\modules\goldoc\models\Product`.
 */
class ProductSearch extends Product
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'venue_id', 'goldoc_manager_id', 'active_manager_id', 'sponsor_id', 'installer_id', 'type_id', 'item_id',
                'colour_id', 'design_id', 'substrate_id', 'width', 'height', 'depth', 'supplier_id', 'sponsor_id',
                'quantity', 'loc', 'product_price', 'product_unit_price', 'labour_price', 'machine_price', 'total_price', 'supplier_priced'], 'safe'],
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
        $query = Product::find()
            ->notDeleted()
            ->joinWith(['venue', 'type', 'item', 'colour', 'design', 'substrate', 'supplier', 'sponsor']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);
        $dataProvider->sort->attributes['venue_id'] = [
            'asc' => ['venue.code' => SORT_ASC],
            'desc' => ['venue.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['type_id'] = [
            'asc' => ['type.code' => SORT_ASC],
            'desc' => ['type.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['item_id'] = [
            'asc' => ['item.code' => SORT_ASC],
            'desc' => ['item.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['colour_id'] = [
            'asc' => ['colour.code' => SORT_ASC],
            'desc' => ['colour.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['design_id'] = [
            'asc' => ['design.code' => SORT_ASC],
            'desc' => ['design.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['substrate_id'] = [
            'asc' => ['substrate.code' => SORT_ASC],
            'desc' => ['substrate.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['supplier_id'] = [
            'asc' => ['supplier.code' => SORT_ASC],
            'desc' => ['supplier.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['sponsor_id'] = [
            'asc' => ['sponsor.code' => SORT_ASC],
            'desc' => ['sponsor.code' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['installer_id'] = [
            'asc' => ['installer.code' => SORT_ASC],
            'desc' => ['installer.code' => SORT_DESC],
        ];

        $this->status = null;

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'product.id' => $this->id,
            'product.quantity' => $this->quantity,
            'product.product_price' => $this->product_price,
            'product.product_unit_price' => $this->product_unit_price,
            'product.labour_price' => $this->labour_price,
            'product.machine_price' => $this->machine_price,
            'product.total_price' => $this->total_price,
        ]);

        $query->andFilterWhere(['like', 'loc', $this->loc]);

        if ($this->supplier_priced !== null) {
            if ($this->supplier_priced === 0 || $this->supplier_priced === "0") {
                $query->andWhere(['or', ['product.supplier_priced' => 0], ['product.supplier_priced' => null]]);
            } else {
                $query->andWhere(['product.supplier_priced' => $this->supplier_priced]);
            }
        }

        $this->multiFilter($query, 'status', 'product.status');
        $this->multiFilter($query, 'venue_id', 'product.venue_id');
        $this->multiFilter($query, 'type_id', 'product.type_id');
        $this->multiFilter($query, 'item_id', 'product.item_id');
        $this->multiFilter($query, 'colour_id', 'product.colour_id');
        $this->multiFilter($query, 'design_id', 'product.design_id');
        $this->multiFilter($query, 'substrate_id', 'product.substrate_id');
        $this->multiFilter($query, 'goldoc_manager_id', 'product.goldoc_manager_id');
        $this->multiFilter($query, 'active_manager_id', 'product.active_manager_id');
        $this->multiFilter($query, 'sponsor_id', 'product.sponsor_id');
        $this->multiFilter($query, 'installer_id', 'product.installer_id');
        $this->multiFilter($query, 'supplier_id', 'product.supplier_id');
        $this->multiFilter($query, 'width', 'product.width');
        $this->multiFilter($query, 'height', 'product.height');
        $this->multiFilter($query, 'depth', 'product.depth');

        return $dataProvider;
    }

    /**
     * @param ProductQuery $query
     * @param $attribute
     * @param $field
     */
    private function multiFilter($query, $attribute, $field)
    {
        if ($this->$attribute) {
            if (is_array($this->$attribute)) {
                $query->andFilterWhere(['in', $field, $this->$attribute]);
            } elseif ($this->$attribute == '-') {
                $query->andWhere([$field => null]);
            } elseif ($this->$attribute == '*') {
                $query->andWhere(['not', [$field => null]]);
            } else {
                $query->andFilterWhere([$field => $this->$attribute]);
            }
        } elseif ($this->$attribute === 0 || $this->$attribute === '0') {
            $query->andWhere(['or', [$field => 0], [$field => null]]);
        }

    }


}
