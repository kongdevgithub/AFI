<?php

namespace app\models\search;

use app\models\Address;
use app\traits\DateSearchTrait;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Job;

/**
 * JobSearch represents the model behind the search form about `app\models\Job`.
 */
class JobSearch extends Job
{
    use DateSearchTrait;

    /**
     * @var
     */
    public $staff_id;

    /**
     * @var
     */
    public $despatch_date_from;

    /**
     * @var
     */
    public $despatch_date_to;

    /**
     * @var
     */
    public $due_date_from;

    /**
     * @var
     */
    public $due_date_to;

    /**
     * @var
     */
    public $shippingAddress;

    /**
     * @var
     */
    public $shippingAddress__state;

    /**
     * @var
     */
    public $client_company_id;

    /**
     * @var
     */
    public $freight_quote_requested;

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
            [['id', 'vid', 'job_type_id', 'company_id', 'contact_id', 'staff_lead_id', 'staff_rep_id', 'staff_csr_id',
                'rollout_id', 'price_structure_id', 'account_term_id', 'staff_id', 'quote_win_chance', 'name',
                'due_date', 'production_date', 'prebuild_date', 'installation_date', 'despatch_date', 'quote_at', 'production_at', 'despatch_at', 'complete_at', 'packed_at', 'status',
                'purchase_order', 'invoice_sent', 'invoice_paid', 'shippingAddress', 'invoice_reference', 'despatch_date_from', 'despatch_date_to',
                'shippingAddress__state', 'client_company_id', 'due_date_from', 'due_date_to', 'freight_quote_requested'], 'safe'],
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
        $query = Job::find()->notDeleted();
        $query->andWhere('job.deleted_at IS NULL');

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
            'job.job_type_id' => $this->job_type_id,
            'job.company_id' => $this->company_id,
            'job.contact_id' => $this->contact_id,
            'job.staff_lead_id' => $this->staff_lead_id,
            'job.staff_rep_id' => $this->staff_rep_id,
            'job.staff_csr_id' => $this->staff_csr_id,
            'job.rollout_id' => $this->rollout_id,
            'job.price_structure_id' => $this->price_structure_id,
            'job.account_term_id' => $this->account_term_id,
            'job.quote_win_chance' => $this->quote_win_chance,
        ]);

        if ($this->id) {
            if (is_array($this->id)) {
                $query->andFilterWhere(['IN', 'job.id', $this->id]);
            } else {
                $query->andFilterWhere(['job.id' => $this->id]);
            }
        }

        if ($this->vid) {
            if (is_array($this->vid)) {
                $query->andFilterWhere(['IN', 'job.vid', $this->vid]);
            } else {
                $query->andFilterWhere(['like', 'job.vid', $this->vid]);
            }
        }

        if ($this->staff_id) {
            $query->andWhere(['or',
                ['job.staff_lead_id' => $this->staff_id],
                ['job.staff_rep_id' => $this->staff_id],
                ['job.staff_csr_id' => $this->staff_id],
            ]);
        }

        //$query->joinWith(['jobType', 'company', 'contact', 'priceStructure', 'accountTerm', 'rollout']);

        $query->andFilterWhere(['like', 'job.name', $this->name])
            ->andFilterWhere(['like', 'job.purchase_order', $this->purchase_order]);


        if ($this->status) {
            if (is_array($this->status)) {
                $query->andFilterWhere(['in', 'job.status', $this->status]);
            } else {
                $query->andFilterWhere(['job.status' => $this->status]);
            }
        }

        if ($this->due_date) {
            $this->dateFilter($query, 'job.due_date', $this->due_date);
        }
        if ($this->due_date_from) {
            $query->andWhere(['>=', 'job.due_date', $this->due_date_from]);
        }
        if ($this->due_date_to) {
            $query->andWhere(['<=', 'job.due_date', $this->due_date_to]);
        }
        if ($this->production_date) {
            $this->dateFilter($query, 'job.production_date', $this->production_date);
        }
        if ($this->prebuild_date) {
            $this->dateFilter($query, 'job.prebuild_date', $this->prebuild_date);
        }
        if ($this->despatch_date) {
            $this->dateFilter($query, 'job.despatch_date', $this->despatch_date);
        }
        if ($this->despatch_date_from) {
            $query->andWhere(['>=', 'job.despatch_date', $this->despatch_date_from]);
        }
        if ($this->despatch_date_to) {
            $query->andWhere(['<=', 'job.despatch_date', $this->despatch_date_to]);
        }
        if ($this->installation_date) {
            $this->dateFilter($query, 'job.installation_date', $this->installation_date);
        }

        if (isset($this->invoice_sent)) {
            if (is_numeric($this->invoice_sent)) {
                if ($this->invoice_sent) {
                    $query->andWhere(['is not', 'job.invoice_sent', null]);
                } else {
                    $query->andWhere(['job.invoice_sent' => null]);
                }
            } elseif ($this->invoice_sent) {
                $this->dateFilter($query, 'job.invoice_sent', $this->invoice_sent);
            }
        }
        if (isset($this->invoice_paid)) {
            if (is_numeric($this->invoice_paid)) {
                if ($this->invoice_paid) {
                    $query->andWhere(['is not', 'job.invoice_paid', null]);
                } else {
                    $query->andWhere(['job.invoice_paid' => null]);
                }
            } elseif ($this->invoice_paid) {
                $this->dateFilter($query, 'job.invoice_paid', $this->invoice_paid);
            }
        }

        if ($this->quote_at) {
            $this->dateFilter($query, 'job.quote_at', $this->quote_at, 'int');
        }
        if ($this->production_at) {
            $this->dateFilter($query, 'job.production_at', $this->production_at, 'int');
        }
        if ($this->despatch_at) {
            $this->dateFilter($query, 'job.despatch_at', $this->despatch_at, 'int');
        }
        if ($this->packed_at) {
            $this->dateFilter($query, 'job.packed_at', $this->packed_at, 'int');
        }
        if ($this->complete_at) {
            $this->dateFilter($query, 'job.complete_at', $this->complete_at, 'int');
        }

        if ($this->shippingAddress !== null) {
            if ($this->shippingAddress === true) {
                $on = 'address.model_id=job.id AND address.model_name=:model_name AND address.type=:type';
                $query->leftJoin('address', $on, [
                    ':model_name' => Job::className(),
                    ':type' => Address::TYPE_SHIPPING,
                ]);
                $query->andWhere('address.id IS NOT NULL');
            } elseif (!$this->shippingAddress) {
                $on = 'address.model_id=job.id AND address.model_name=:model_name AND address.type=:type';
                $query->leftJoin('address', $on, [
                    ':model_name' => Job::className(),
                    ':type' => Address::TYPE_SHIPPING,
                ]);
                $query->andWhere('address.id IS NULL');
            } else {
                $on = 'id=:id AND address.model_id=job.id AND address.model_name=:model_name AND address.type=:type';
                $query->leftJoin('address', $on, [
                    ':id' => $this->shippingAddress,
                    ':model_name' => Job::className(),
                    ':type' => Address::TYPE_SHIPPING,
                ]);
                $query->andWhere(['address.model_id' => $this->shippingAddress]);
            }
        }

        if ($this->shippingAddress__state) {
            $on = 'state=:state AND address.model_id=job.id AND address.model_name=:model_name AND address.type=:type';
            $query->leftJoin('address', $on, [
                ':state' => $this->shippingAddress__state,
                ':model_name' => Job::className(),
                ':type' => Address::TYPE_SHIPPING,
            ]);
            $query->andWhere(['address.state' => $this->shippingAddress__state]);
        }

        if ($this->client_company_id) {
            $query->andFilterWhere(['job.company_id' => $this->client_company_id]);
        }

        if ($this->invoice_reference) {
            $query->andFilterWhere(['LIKE', 'job.invoice_reference', $this->invoice_reference]);
        }

        if ($this->freight_quote_requested) {
            $query->andWhere(['job.freight_quote_provided_at' => null]);
            $query->andWhere(['NOT', ['job.freight_quote_requested_at' => null]]);
        }

        //debug($query->createCommand()->getRawSql());
        //die;

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['shippingAddress__state'] = Yii::t('app', 'State');
        return $attributeLabels;
    }


}