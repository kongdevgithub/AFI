<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Attachment;

/**
 * AttachmentSearch represents the model behind the search form about `app\models\Attachment`.
 */
class AttachmentSearch extends Attachment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'model_id', 'filesize', 'sort_order', 'created_at', 'created_by', 'updated_by', 'updated_at', 'deleted_at'], 'integer'],
            [['model_name', 'filename', 'extension', 'filetype', 'notes'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
// bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Attachment::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'model_id' => $this->model_id,
            'filesize' => $this->filesize,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['like', 'model_name', $this->model_name])
            ->andFilterWhere(['like', 'filename', $this->filename])
            ->andFilterWhere(['like', 'extension', $this->extension])
            ->andFilterWhere(['like', 'filetype', $this->filetype])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}