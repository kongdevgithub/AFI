<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/e0080b9d6ffa35acb85312bf99a557f2
 *
 * @package default
 */


namespace app\modules\goldoc\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\goldoc\models\Supplier;

/**
 * SupplierSearch represents the model behind the search form about `app\modules\goldoc\models\Supplier`.
 */
class SupplierSearch extends Supplier
{

	/**
	 *
	 * @inheritdoc
	 * @return unknown
	 */
	public function scenarios() {
		// bypass parent scenarios
		return Model::scenarios();
	}


	/**
	 *
	 * @inheritdoc
	 * @return unknown
	 */
	public function rules() {
		return [
			[['id'], 'integer'],
			[['code', 'name'], 'safe'],
		];
	}


	/**
	 * Creates data provider instance with search query applied
	 *
	 *
	 * @param array   $params
	 * @return ActiveDataProvider
	 */
	public function search($params) {
		$query = Supplier::find();

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
			]);

		$query->andFilterWhere(['like', 'code', $this->code])
		->andFilterWhere(['like', 'name', $this->name]);

		return $dataProvider;
	}


}
