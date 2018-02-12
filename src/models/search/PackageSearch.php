<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Package;

/**
 * PackageSearch represents the model behind the search form about `app\models\Package`.
 */
class PackageSearch extends Package
{

    /**
     * @var string
     */
    public $address__name;

    /**
     * @var string
     */
    public $address__street;

    /**
     * @var string
     */
    public $address__postcode;

    /**
     * @var string
     */
    public $address__city;

    /**
     * @var string
     */
    public $address__state;

    /**
     * @var string
     */
    public $address__country;

    /**
     * @var string
     */
    public $address__contact;

    /**
     * @var string
     */
    public $address__phone;

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
            [['id', 'pickup_id', 'overflow_package_id'], 'integer'],
            [['status'], 'safe'],
            [['address__name', 'address__street', 'address__postcode', 'address__city', 'address__state', 'address__country', 'address__contact', 'address__phone'], 'safe'],
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
        $query = Package::find()->notDeleted();
        $query->andWhere('package.deleted_at IS NULL');

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

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['in', 'package.id', $this->id]);
            } else {
                $query->andFilterWhere(['package.id' => $this->id]);
            }
        }

        if ($this->pickup_id) {
            if (is_array($this->pickup_id)) {
                $query->andFilterWhere(['in', 'package.pickup_id', $this->pickup_id]);
            } else {
                $query->andFilterWhere(['package.pickup_id' => $this->pickup_id]);
            }
        }

        if ($this->overflow_package_id) {
            if (is_array($this->overflow_package_id)) {
                $query->andFilterWhere(['in', 'package.overflow_package_id', $this->overflow_package_id]);
            } else {
                $query->andFilterWhere(['package.overflow_package_id' => $this->overflow_package_id]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'package.status', $this->status]);
            } else {
                $query->andFilterWhere(['package.status' => $this->status]);
            }
        }

        $query->joinWith('addressSingle');
        if ($this->address__name) {
            $query->andFilterWhere(['like', 'address.name', $this->address__name]);
        }
        if ($this->address__street) {
            $query->andFilterWhere(['like', 'address.street', $this->address__street]);
        }
        if ($this->address__postcode) {
            $query->andFilterWhere(['address.postcode' => $this->address__postcode]);
        }
        if ($this->address__city) {
            $query->andFilterWhere(['address.city' => $this->address__city]);
        }
        if ($this->address__state) {
            $query->andFilterWhere(['address.state' => $this->address__state]);
        }
        if ($this->address__country) {
            $query->andFilterWhere(['address.country' => $this->address__country]);
        }
        if ($this->address__contact) {
            $query->andFilterWhere(['like', 'address.contact', $this->address__contact]);
        }
        if ($this->address__phone) {
            $query->andFilterWhere(['address.phone' => $this->address__phone]);
        }

        //debug($query->createCommand()->getRawSql());
        //die;

        return $dataProvider;
    }

}