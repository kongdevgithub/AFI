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
use app\models\Notification;

/**
 * NotificationSearch represents the model behind the search form about `app\models\Notification`.
 */
class NotificationSearch extends Notification
{

	/**
	 *
	 * @inheritdoc
	 * @return unknown
	 */
	public function rules() {
		return [
			[['id', 'model_id', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_at'], 'integer'],
			[['model_name', 'title', 'body', 'type'], 'safe'],
		];
	}


	/**
	 *
	 * @inheritdoc
	 * @return unknown
	 */
	public function scenarios() {
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}


	/**
	 * Creates data provider instance with search query applied
	 *
	 *
	 * @param array   $params
	 * @return ActiveDataProvider
	 */
	public function search($params) {
		$query = Notification::find();

		$dataProvider = new ActiveDataProvider([
				'query' => $query,
			]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$query->andFilterWhere([
				'id' => $this->id,
				'model_id' => $this->model_id,
				'created_by' => $this->created_by,
				'created_at' => $this->created_at,
				'updated_by' => $this->updated_by,
				'updated_at' => $this->updated_at,
				'deleted_at' => $this->deleted_at,
			]);

		$query->andFilterWhere(['like', 'model_name', $this->model_name])
		->andFilterWhere(['like', 'title', $this->title])
		->andFilterWhere(['like', 'body', $this->body])
		->andFilterWhere(['like', 'type', $this->type]);

		return $dataProvider;
	}


}
