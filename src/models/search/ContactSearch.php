<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Contact;

/**
 * ContactSearch represents the model behind the search form about `app\models\Contact`.
 */
class ContactSearch extends Contact
{

    /**
     * @var string
     */
    public $keywords;

    /**
     * @var string
     */
    public $company_id;

    /**
     * @var string
     */
    public $company__name;

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
            [['id', 'company_id', 'default_company_id', 'keywords', 'company__name', 'first_name', 'last_name', 'status', 'phone', 'email'], 'safe'],
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
        $query = Contact::find()->notDeleted();

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
                $query->andFilterWhere(['IN', 'contact.id', $this->id]);
            } else {
                $query->andFilterWhere(['contact.id' => $this->id]);
            }
        }

        if ($this->default_company_id) {
            if (is_array($this->default_company_id)) {
                $query->andFilterWhere(['IN', 'contact.default_company_id', $this->default_company_id]);
            } else {
                $query->andFilterWhere(['contact.default_company_id' => $this->default_company_id]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['IN', 'contact.status', $this->status]);
            } else {
                $query->andFilterWhere(['contact.status' => $this->status]);
            }
        }

        $query->andFilterWhere(['like', 'contact.first_name', $this->first_name])
            ->andFilterWhere(['like', 'contact.last_name', $this->last_name])
            ->andFilterWhere(['like', 'contact.phone', $this->phone])
            ->andFilterWhere(['like', 'contact.email', $this->email]);

        if ($this->company_id) {
            $query->joinWith(['companies']);
            $query->andFilterWhere(['company.id' => $this->company_id]);
        }

        if ($this->company__name) {
            $query->joinWith(['companies']);
            $query->andFilterWhere(['like', 'company.name', $this->company__name]);
        }

        if ($this->keywords) {
            foreach (explode(' ', $this->keywords) as $keyword) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'first_name', $keyword],
                    ['like', 'last_name', $keyword],
                    ['like', 'email', $keyword],
                ]);
            }
        }

        return $dataProvider;
    }

}