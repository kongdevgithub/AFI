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
use app\models\CompanyRate;

/**
 * CompanyRateSearch represents the model behind the search form about `app\models\CompanyRate`.
 */
class CompanyRateSearch extends CompanyRate
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
            [['id', 'company_id', 'product_type_id', 'item_type_id', 'option_id', 'component_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['price'], 'number'],
        ];
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = CompanyRate::find()->notDeleted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'product_type_id' => $this->product_type_id,
            'item_type_id' => $this->item_type_id,
            'option_id' => $this->option_id,
            'component_id' => $this->component_id,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        return $dataProvider;
    }


}
