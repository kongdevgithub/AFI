<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pickup;

/**
 * PickupSearch represents the model behind the search form about `app\models\Pickup`.
 */
class PickupSearch extends Pickup
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
            [['id', 'carrier_id'], 'integer'],
            [['status', 'without_email'], 'safe'],
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
        $query = Pickup::find()->notDeleted();
        $query->andWhere('pickup.deleted_at IS NULL');

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
            'pickup.id' => $this->id,
            'pickup.carrier_id' => $this->carrier_id,
        ]);

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'pickup.status', $this->status]);
            } else {
                $query->andFilterWhere(['pickup.status' => $this->status]);
            }
        }

        return $dataProvider;
    }

}