<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Company;

/**
 * CompanySearch represents the model behind the search form about `app\models\Company`.
 */
class CompanySearch extends Company
{

    /**
     * @var string
     */
    public $keywords;

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
            [['id', 'staff_rep_id', 'price_structure_id', 'account_term_id', 'job_type_id', 'industry_id', 'default_contact_id', 'keywords', 'name', 'status', 'website'], 'safe'],
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
        $query = Company::find()
            ->notDeleted();

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
            'default_contact_id' => $this->default_contact_id,
        ]);

        $query->andFilterWhere(['like', 'company.name', $this->name])
            ->andFilterWhere(['like', 'company.website', $this->website]);

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['IN', 'company.id', $this->id]);
            } else {
                $query->andFilterWhere(['company.id' => $this->id]);
            }
        }

        if ($this->staff_rep_id) {
            if (is_array($this->staff_rep_id)) {
                $query->andFilterWhere(['IN', 'company.staff_rep_id', $this->staff_rep_id]);
            } else {
                $query->andFilterWhere(['company.staff_rep_id' => $this->staff_rep_id]);
            }
        }

        if ($this->account_term_id) {
            if (is_array($this->account_term_id)) {
                $query->andFilterWhere(['IN', 'company.account_term_id', $this->account_term_id]);
            } else {
                $query->andFilterWhere(['company.account_term_id' => $this->account_term_id]);
            }
        }

        if ($this->price_structure_id) {
            if (is_array($this->price_structure_id)) {
                $query->andFilterWhere(['IN', 'company.price_structure_id', $this->price_structure_id]);
            } else {
                $query->andFilterWhere(['company.price_structure_id' => $this->price_structure_id]);
            }
        }

        if ($this->job_type_id) {
            if (is_array($this->job_type_id)) {
                $query->andFilterWhere(['IN', 'company.job_type_id', $this->job_type_id]);
            } else {
                $query->andFilterWhere(['company.job_type_id' => $this->job_type_id]);
            }
        }

        if ($this->industry_id) {
            if (is_array($this->industry_id)) {
                $query->andFilterWhere(['IN', 'company.industry_id', $this->industry_id]);
            } else {
                $query->andFilterWhere(['company.industry_id' => $this->industry_id]);
            }
        }

        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['IN', 'company.status', $this->status]);
            } else {
                $query->andFilterWhere(['company.status' => $this->status]);
            }
        }

        if ($this->keywords) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $this->keywords],
            ]);
        }

        return $dataProvider;
    }

}