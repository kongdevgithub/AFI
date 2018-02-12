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
use app\modules\goldoc\models\SignageWayfinding;

/**
 * SignageWayfindingSearch represents the model behind the search form about `app\modules\goldoc\models\SignageWayfinding`.
 */
class SignageWayfindingSearch extends SignageWayfinding
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
			[['id', 'quantity'], 'integer'],
			[['batch', 'sign_id', 'sign_code', 'level', 'message_side_1', 'message_side_2', 'fixing', 'notes'], 'safe'],
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
		$query = SignageWayfinding::find();

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
				'quantity' => $this->quantity,
			]);

		$query->andFilterWhere(['like', 'batch', $this->batch])
		->andFilterWhere(['like', 'sign_id', $this->sign_id])
		->andFilterWhere(['like', 'sign_code', $this->sign_code])
		->andFilterWhere(['like', 'level', $this->level])
		->andFilterWhere(['like', 'message_side_1', $this->message_side_1])
		->andFilterWhere(['like', 'message_side_2', $this->message_side_2])
		->andFilterWhere(['like', 'fixing', $this->fixing])
		->andFilterWhere(['like', 'notes', $this->notes]);

		return $dataProvider;
	}


}
