<?php

namespace app\models\search;

use dektrium\rbac\models\Assignment;
use dektrium\user\models\UserSearch as BaseUserSearch;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class UserSearch
 * @package app\models\search
 */
class UserSearch extends BaseUserSearch
{

    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['fieldsSafe'][0][] = 'name';
        $rules['fieldsSafe'][0][] = 'role';
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['name'] = Yii::t('app', 'Name');
        $attributeLabels['role'] = Yii::t('app', 'Role');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function search($params)
    {
        $dataProvider = parent::search($params);
        $dataProvider->pagination->pageSize = 1000;
        /** @var ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith(['profile']);
        $query->andFilterWhere(['like', 'profile.name', $this->name]);
        if ($this->role) {
            $userIds = Yii::$app->authManager->getUserIdsByRole($this->role);
            $query->andWhere(['id' => $userIds]);
        }
        return $dataProvider;
    }

}